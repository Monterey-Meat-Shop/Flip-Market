<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';
    protected $primaryKey = 'discountID';

    protected $fillable = [
        //'productID',
        'name',
        'discount_type',
        'discount_value',
        'is_active',
        //'applies_to'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product', 'discount_id', 'product_id');
    }

    // public function orders()
    // {
    //     return $this->belongsTo(Order::class, 'orderID');
    // }

    public function getFinalPrice(float $originalPrice): float
    {
        // Check if the discount is active before applying it
        if (!$this->is_active) {
            return $originalPrice;
        }

        if ($this->discount_type === 'Percentage') {
            // Calculate percentage discount
            return $originalPrice - ($originalPrice * ($this->discount_value / 100));

        } elseif ($this->discount_type === 'Fixed') {
            // Calculate fixed amount discount
            return $originalPrice - $this->discount_value;
        }

        // Return the original price if no valid discount type is found
        return $originalPrice;
    }

}
