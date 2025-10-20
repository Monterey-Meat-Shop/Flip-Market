<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'orderID';

    protected $fillable = [
        'customerID',
        'discountID',
        'order_date',
        'total_amount',
        'final_amount',
        'order_status',
        'address_choice',
        'postal_code',
        'city',
        'province',
        'payment_status',
        'stock_deducted',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'stock_deducted' => 'boolean',
    ];

    protected $with = ['payment', 'customer', 'orderItems', 'shipping'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discountID', 'discountID');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'orderID', 'orderID');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'orderID', 'orderID');
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class, 'orderID', 'orderID');
    }

    public function deductStockForTransaction(): void
    {
        if ($this->stock_deducted) {
            return;
        }

        $this->load('orderItems.productVariant', 'orderItems.product');

        foreach ($this->orderItems as $item) {
            if ($item->productVariant) {
                $item->productVariant->decrement('stock_quantity', $item->quantity);
                // ensure product status exists
                if (method_exists($item->productVariant->product, 'refreshProductStatus')) {
                    $item->productVariant->product->refreshProductStatus();
                }
            } elseif ($item->product) {
                $item->product->decrement('stock_quantity', $item->quantity);
                if (method_exists($item->product, 'refreshProductStatus')) {
                    $item->product->refreshProductStatus();
                }
            }
        }

        // mark as done so we don't deduct again
        $this->stock_deducted = true;
        $this->saveQuietly();
    }

    public function deductStockForPendingOrder(): void
    {
        if ($this->stock_deducted) {
            return;
        }

        $this->load('orderItems.productVariant', 'orderItems.product');

        foreach ($this->orderItems as $item) {
            if ($item->productVariant) {
                $item->productVariant->decrement('stock_quantity', $item->quantity);
                if (method_exists($item->productVariant->product, 'refreshProductStatus')) {
                    $item->productVariant->product->refreshProductStatus();
                }
            } elseif ($item->product) {
                $item->product->decrement('stock_quantity', $item->quantity);
                if (method_exists($item->product, 'refreshProductStatus')) {
                    $item->product->refreshProductStatus();
                }
            }
        }

        // mark as done
        $this->stock_deducted = true;
        $this->saveQuietly();
    }
}