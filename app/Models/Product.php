<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'products';
    protected $primaryKey = 'productID';

    protected $fillable = [
        'categoryID',
        'brandID',
        'name',
        'slug',
        'description',
        'price',
        'image_url',
        'status', 
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'image_url' => 'array',
        'is_active' => 'boolean',
    ];

    // --- Relationships ---
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'productID');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoryID');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brandID');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'productID', 'productID');
    }

    // public function discounts(): BelongsToMany
    // {
    //     return $this->belongsToMany(Discount::class, 'discount_product', 'product_id', 'discount_id');
    // }

    public function discounts()
    {
    return $this->belongsToMany(Discount::class, 'discount_product', 'product_id', 'discount_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discountID');
    }
    // --- Attributes / Helpers ---
    public function getTotalStockQuantityAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->sum('stock_quantity');
        }
        return (int) $this->variants()->sum('stock_quantity');
    }

    public function getCalculatedStatusAttribute(): string
    {
        if (($this->attributes['status'] ?? null) === 'pre_order') {
            return 'pre_order';
        }

        $totalStock = (int) $this->variants()->sum('stock_quantity');

        if ($totalStock === 0) {
            return 'out_of_stock';
        } elseif ($totalStock <= 4) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStatusAttribute($value): string
    {
        if ($value === 'pre_order') {
            return 'pre_order';
        }

        return $this->calculated_status;
    }

    public function getIsPreOrderAttribute(): bool
    {
        return $this->status === 'pre_order';
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->status === 'in_stock' && $this->total_stock_quantity > 0;
    }

    // --- Boot logic for status/is_active ---
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($product) {
            $totalStock = $product->variants()->sum('stock_quantity');

            if (($product->attributes['status'] ?? null) !== 'pre_order') {
                if ($totalStock === 0) {
                    $product->status = 'out_of_stock';
                } elseif ($totalStock <= 4) {
                    $product->status = 'low_stock';
                } else {
                    $product->status = 'in_stock';
                }
            }

            $product->is_active = ($product->status === 'pre_order') || ($totalStock > 0);

            if ($product->isDirty(['status', 'is_active'])) {
                $product->saveQuietly();
            }
        });
    }

    /**
     * Scope: Get top performing products by sales & revenue
     */
    public function scopeTopPerforming($query, int $limit = 6)
    {
        $orderItemsTable = 'order_items';

        // If order_items table doesn't exist, just return limited products
        if (!Schema::hasTable($orderItemsTable)) {
            return $query->limit($limit);
        }

        // Detect columns
        $quantityCol = Schema::hasColumn($orderItemsTable, 'quantity') ? 'quantity' : null;

        // Find revenue/amount column candidates
        $amountCandidates = ['total', 'subtotal', 'amount', 'line_total', 'row_total', 'unit_price', 'price'];
        $amountCol = null;
        foreach ($amountCandidates as $c) {
            if (Schema::hasColumn($orderItemsTable, $c)) {
                $amountCol = $c;
                break;
            }
        }

        // Build revenue expression
        if ($amountCol === 'total' || in_array($amountCol, ['subtotal','amount','line_total','row_total'])) {
            $revenueExpr = "SUM({$orderItemsTable}.{$amountCol})";
        } elseif ($amountCol && $quantityCol) {
            $revenueExpr = "SUM({$orderItemsTable}.{$amountCol} * {$orderItemsTable}.{$quantityCol})";
        } elseif ($amountCol) {
            $revenueExpr = "SUM(COALESCE({$orderItemsTable}.{$amountCol},0))";
        } else {
            $revenueExpr = "0";
        }

        // Build sales expression
        if ($quantityCol) {
            $salesExpr = "SUM({$orderItemsTable}.{$quantityCol})";
        } else {
            $idCol = Schema::hasColumn($orderItemsTable, 'id') ? 'id' : 'productID';
            $salesExpr = "COUNT({$orderItemsTable}.{$idCol})";
        }

        // Subquery: aggregate sales & revenue by productID
        $sub = DB::table($orderItemsTable)
            ->select(
                "{$orderItemsTable}.productID",
                DB::raw("{$salesExpr} as total_sales"),
                DB::raw("{$revenueExpr} as total_revenue")
            )
            ->groupBy("{$orderItemsTable}.productID");

        // Left join the subquery with products
        return $query->leftJoinSub($sub, 'oi', 'oi.productID', '=', 'products.productID')
            ->select(
                'products.*',
                DB::raw('COALESCE(oi.total_sales, 0) AS total_sales'),
                DB::raw('COALESCE(oi.total_revenue, 0) AS total_revenue')
            )
            ->orderByDesc('total_sales')
            ->limit($limit);
    }

    public function getImagePathAttribute()
    {
        // Check if image_url exists and is not null
        if (!$this->image_url) {
            return null;
        }

        // If image_url is stored as JSON array
        if (is_string($this->image_url)) {
            $decoded = json_decode($this->image_url, true);
            return is_array($decoded) && isset($decoded[0]) ? $decoded[0] : $this->image_url;
        }

        // If image_url is already an array (due to casting)
        if (is_array($this->image_url) && isset($this->image_url[0])) {
            return $this->image_url[0];
        }

        // If it's a simple string
        return $this->image_url;
    }

    public function getDiscountedPriceAttribute(): float
    {
        // Find the first active discount linked to this product
        $activeDiscount = $this->discounts()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->first();

        if (! $activeDiscount) {
            return (float) $this->price;
        }

        // Compute discounted price based on type
        if ($activeDiscount->discount_type === 'Percentage') {
            $discounted = $this->price - ($this->price * ($activeDiscount->discount_value / 100));
        } else {
            $discounted = $this->price - $activeDiscount->discount_value;
        }

        return max($discounted, 0); // prevent negative price
    }
}
