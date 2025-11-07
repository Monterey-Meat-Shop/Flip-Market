<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'colorway',
        'stock_quantity',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'productID');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($variant) {
            self::updateStockStatus($variant);
        });

        static::deleted(function ($variant) {
            self::updateStockStatus($variant);
        });
    }

    protected static function updateStockStatus($variant): void
    {
        $product = $variant->product;
        if (!$product) return;

        $totalStock = (int) $product->variants()->sum('stock_quantity');

        if ($product->status !== 'pre_order') {
            if ($totalStock === 0) {
                $product->status = 'out_of_stock';
            } elseif ($totalStock <= 4) {
                $product->status = 'low_stock';
            } else {
                $product->status = 'in_stock';
            }
        }

        if ($product->status === 'pre_order') {
            $product->is_active = true;
        }

        if ($product->isDirty(['status'])) {
            $product->saveQuietly();
        }
    }
}
