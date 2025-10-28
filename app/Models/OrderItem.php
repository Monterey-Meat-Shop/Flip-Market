<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'order_items';
    protected $primaryKey = 'order_itemID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'orderID',
        'productID',
        'product_variant_id',
        'discountID', 
        'size', 
        'colorway',
        'quantity',
        'unit_price',
        'original_price',
        'discount_name',
        'discount_amount',
        'sub_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discountID', 'discountID');
    }
}
