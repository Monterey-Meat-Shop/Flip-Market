<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'products';
    protected $primaryKey = 'ProductID';

    protected $fillable = [
        'CategoryID',
        'BrandID',
        'Name',
        'Slug',
        'Description',
        'Price',
        'image_url',
        'Stock_Quantity',
        'Size',
        'Colorway',
        'Status', 
    ];

    protected $casts = [
        'Price' => 'decimal:2',
        'Size' => 'array',
        'image_url' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving (function ($product) {
            $product->is_active = $product->Stock_Quantity > 0;
        });

    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'BrandID');
    }

    public function orderItems(){
        //return $this->hasMany(OrderItem::class);
    }
}
