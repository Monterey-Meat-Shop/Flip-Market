<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_items';
    protected $primaryKey = 'cart_itemID';
    public $timestamps = true;
    protected $keyType = 'int';

    protected $fillable = [
        'customerID',
        'productID',
        'product_variant_id',
        'size',
        'colorway',
        'quantity',
        'unit_price',
        'sub_total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
