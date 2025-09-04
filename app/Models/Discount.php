<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'discounts';
    protected $primaryKey = 'discountID';

    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product', 'discount_id', 'product_id');
    }

    public function getFinalPrice(float $originalPrice): float
    {
        if (!$this->is_active) {
            return $originalPrice;
        }

        if ($this->discount_type === 'Percentage') {
            return $originalPrice - ($originalPrice * ($this->discount_value / 100));

        } elseif ($this->discount_type === 'Fixed') {
            return $originalPrice - $this->discount_value;
        }

        return $originalPrice;
    }

    protected static function booted()
    {
        static::deleting(function (Discount $discount) {
            // Corrected variable name from $brand to $discount
            if (! $discount->isForceDeleting()) {
                $discount->is_active = false;
                $discount->saveQuietly();
            }
        });
    }
}
