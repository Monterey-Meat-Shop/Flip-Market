<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryID');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brandID');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'productID', 'productID');
    }

    public function getIsPreOrderAttribute(): bool
    {
        return $this->status === 'pre_order';
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->status === 'in_stock' && $this->total_stock_quantity > 0;
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_product', 'product_id', 'discount_id');
    }

    /**
     * Refresh the product status based on total variant stock.
     */
    public function refreshProductStatus(): void
    {
        $totalStock = $this->variants()->sum('stock_quantity');

        if ($this->status !== 'pre_order') {
            if ($totalStock === 0) {
                $this->status = 'out_of_stock';
            } elseif ($totalStock <= 4) {
                $this->status = 'low_stock';
            } else {
                $this->status = 'in_stock';
            }
        }

        $this->is_active = ($this->status === 'pre_order') || ($totalStock > 0);

        if ($this->isDirty(['status', 'is_active'])) {
            $this->saveQuietly();
        }
    }
}
