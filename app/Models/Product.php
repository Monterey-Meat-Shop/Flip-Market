<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'stock_quantity',
        'size',
        'colorway',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'size' => 'array',
        'image_url' => 'array',
        'is_active' => 'boolean',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving (function ($product) {
    //         // Only update is_active if the product is not in the process of being deleted
    //         if (!$product->isDirty('deleted_at')) {
    //             $product->is_active = ($product->status === 'pre_order') || ($product->stock_quantity > 0);
    //         }
    //     });

    // }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryID');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brandID');
    }

    // public function orderItems(){
    //     //return $this->hasMany(OrderItem::class);
    // }

    // public function getIsPreOrderAttribute(): bool
    // {
    //     return $this->status === 'pre_order';
    // }

    // public function getIsInStockAttribute(): bool
    // {
    //     return $this->status === 'in_stock' && $this->stock_quantity > 0;
    // }
    
}