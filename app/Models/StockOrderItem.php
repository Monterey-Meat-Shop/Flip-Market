<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOrderItem extends Model
{
    protected $fillable = [
        'stock_order_id',
        'product_id',
        'product_variant_id',
        'stock_quantity',
        'stock_quantity_received',
    ];

    public function stockOrder()
    {
        return $this->belongsTo(StockOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'productID');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
