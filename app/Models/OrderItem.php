<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'order_items';

    protected $primaryKey = 'order_itemID';

    protected $fillable = [
        'orderID',
        'productID',
        'product_variant_id', 
        'size', 
        'colorway',
        'quantity',
        'unit_price',
        'sub_total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    // Add a relationship to the ProductVariant model for easier access
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }
}
