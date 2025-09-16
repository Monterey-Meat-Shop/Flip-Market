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

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_product', 'product_id', 'discount_id');
    }

    // --- Attributes / helpers ---
    public function getTotalStockQuantityAttribute(): int
    {
        // eager-loaded variants sum, or fallback to query
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

    // --- Boot ----
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($product) {
            // keep automatic status/is_active logic, but avoid endless saves
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
     * Scope: topPerforming
     *
     * Usage:
     *   Product::topPerforming(6)->get(); // top 6 products by units sold (fallbacks applied)
     *
     * This builds a subquery on `order_items` to aggregate total_sales and total_revenue.
     * It tries to be robust: if `order_items.total` exists it uses that; otherwise uses
     * price * quantity if available; otherwise falls back to counts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopPerforming($query, int $limit = 6)
    {
        $orderItemsTable = 'order_items';

        // if table doesn't exist, just return product rows (no aggregates)
        if (! Schema::hasTable($orderItemsTable)) {
            return $query->limit($limit);
        }

        // detect columns
        $quantityCol = Schema::hasColumn($orderItemsTable, 'quantity') ? 'quantity' : null;

        // find numeric/amount column candidates
        $amountCandidates = ['total', 'subtotal', 'amount', 'line_total', 'row_total', 'price', 'unit_price'];
        $amountCol = null;
        foreach ($amountCandidates as $c) {
            if (Schema::hasColumn($orderItemsTable, $c)) {
                $amountCol = $c;
                break;
            }
        }

        // build aggregate expressions for revenue and sales
        if ($amountCol === 'total' || in_array($amountCol, ['subtotal','amount','line_total','row_total'])) {
            $revenueExpr = "SUM({$orderItemsTable}.{$amountCol})";
        } elseif ($amountCol === 'price' && $quantityCol) {
            $revenueExpr = "SUM({$orderItemsTable}.price * {$orderItemsTable}.{$quantityCol})";
        } elseif ($amountCol) {
            // amount present but quantity may or may not exist
            if ($quantityCol) {
                $revenueExpr = "SUM(COALESCE({$orderItemsTable}.{$amountCol},0) * {$orderItemsTable}.{$quantityCol})";
            } else {
                $revenueExpr = "SUM(COALESCE({$orderItemsTable}.{$amountCol},0))";
            }
        } else {
            // no recognizable amount column, fallback to 0 revenue
            $revenueExpr = "0";
        }

        if ($quantityCol) {
            $salesExpr = "SUM({$orderItemsTable}.{$quantityCol})";
        } else {
            // fallback to counting rows for units sold
            // prefer an id column if present, else count productID occurrences
            $idCol = Schema::hasColumn($orderItemsTable, 'id') ? 'id' : 'productID';
            $salesExpr = "COUNT({$orderItemsTable}.{$idCol})";
        }

        // build the subquery that aggregates by productID
        $sub = DB::table($orderItemsTable)
            ->select(
                "{$orderItemsTable}.productID as productID",
                DB::raw("{$salesExpr} as total_sales"),
                DB::raw("{$revenueExpr} as total_revenue")
            )
            ->groupBy("{$orderItemsTable}.productID");

        // Left join subquery to products and select.
        // We select products.* and aggregate columns from subquery (coalesced to 0).
        $joined = $query->leftJoinSub($sub, 'oi', 'oi.productID', '=', 'products.productID')
            ->select(
                'products.*',
                DB::raw('COALESCE(oi.total_sales, 0) AS total_sales'),
                DB::raw('COALESCE(oi.total_revenue, 0) AS total_revenue')
            )
            ->orderByDesc('total_sales')
            ->limit($limit);

        return $joined;
    }
}
