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

    /**
     * Define the relationship to product variants.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'productID');
    }

    /**
     * Get the total stock quantity from all variants.
     */
    public function getTotalStockQuantityAttribute(): int
    {
        return $this->variants->sum('stock_quantity');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($product) {
            $totalStock = $product->variants()->sum('stock_quantity');

            if ($product->status !== 'pre_order') {
                if ($totalStock === 0) {
                    $product->status = 'out_of_stock';
                } elseif ($totalStock <= 4) {
                    $product->status = 'low_stock';
                } else {
                    $product->status = 'in_stock';
                }
            }

            $product->is_active = ($product->status === 'pre_order') || ($totalStock > 0);

            // Save again only if something changed
            if ($product->isDirty(['status', 'is_active'])) {
                $product->saveQuietly();
            }
        });
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
}
