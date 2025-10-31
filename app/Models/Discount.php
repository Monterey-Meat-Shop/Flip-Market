<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Product;

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
        'start_date',   
        'end_date',     
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product', 'discount_id', 'product_id');
    }

    public function getFinalPrice(float $originalPrice): float
    {
        if (
            ! $this->is_active ||
            ($this->start_date && now()->lt($this->start_date)) ||
            ($this->end_date && now()->gt($this->end_date))
        ) {
            return $originalPrice;
        }

        if ($this->discount_type === 'Percentage') {
            return $originalPrice - ($originalPrice * ($this->discount_value / 100));
        } elseif ($this->discount_type === 'Fixed') {
            return max(0, $originalPrice - $this->discount_value); // prevent negative price
        }

        return $originalPrice;
    }

    protected static function booted()
    {
        parent::boot();
    
        static::saving(function (Discount $discount) {
            if ($discount->end_date && now()->greaterThan($discount->end_date)) {
                $discount->is_active = false;
            }

            if ($discount->is_active && $discount->products()->exists()) {
                $productIds = $discount->products->pluck('productID')->toArray();

                $alreadyDiscounted = \App\Models\Product::whereIn('productID', $productIds)
                    ->whereHas('discounts', function ($query) use ($discount) {
                        $query->where('is_active', true)
                            ->where(function ($q) {
                                $q->whereNull('end_date')->orWhere('end_date', '>', now());
                            })
                            ->where('discounts.discountID', '!=', $discount->discountID);
                    })
                    ->exists();

                if ($alreadyDiscounted) {
                    throw new \Exception('Some products already have an active discount.');
                }
            }
        });

        static::deleting(function (Discount $discount) {
            if (!$discount->isForceDeleting()) {
                $discount->is_active = false;
                $discount->saveQuietly();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }
}
