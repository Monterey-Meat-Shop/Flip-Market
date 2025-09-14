<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'productID');
    }

    public function getTotalStockQuantityAttribute(): int
    {
        return $this->variants->sum('stock_quantity');
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

    /**
     * Get the calculated status based on stock levels
     */
    public function getCalculatedStatusAttribute(): string
    {
        // If status is explicitly set to pre_order, return it
        if ($this->attributes['status'] === 'pre_order') {
            return 'pre_order';
        }

        // Calculate total stock from variants
        $totalStock = (int) $this->variants()->sum('stock_quantity');

        if ($totalStock === 0) {
            return 'out_of_stock';
        } elseif ($totalStock <= 4) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Override the status attribute to return calculated status when not pre_order
     */
    public function getStatusAttribute($value): string
    {
        // If status is explicitly set to pre_order, return it
        if ($value === 'pre_order') {
            return 'pre_order';
        }

        // Otherwise return calculated status
        return $this->calculated_status;
    }

    public function getIsPreOrderAttribute(): bool
    {
        return $this->status === 'pre_order';
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->calculated_status === 'in_stock';
    }

    /**
     * Refresh the product status based on total variant stock.
     * This method updates the database - should be called from observers
     */
    public function refreshProductStatus(): void
    {
        $totalStock = $this->variants()->sum('stock_quantity');

        // Only update status in database if it's not pre_order
        if ($this->attributes['status'] !== 'pre_order') {
            if ($totalStock === 0) {
                $this->attributes['status'] = 'out_of_stock';
            } elseif ($totalStock <= 4) {
                $this->attributes['status'] = 'low_stock';
            } else {
                $this->attributes['status'] = 'in_stock';
            }
        }

        // Products should always be active (as per your requirement)
        $this->is_active = true;

        if ($this->isDirty(['status', 'is_active'])) {
            $this->saveQuietly();
        }
    }
}