<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
    ];

    protected $casts = [
        'order_date' => 'datetime',
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

    public function shipping()
    {
        return $this->hasOne(Shipping::class, 'orderID', 'orderID');
    }

    public function deductStock(): void
    {
        DB::transaction(function () {
            foreach ($this->orderItems as $item) {
                if ($item->productVariant) {
                    $variant = $item->productVariant()->lockForUpdate()->first();

                    if ($variant->stock_quantity < $item->quantity) {
                        throw new \Exception(
                            "Insufficient stock for {$variant->product->name} (Variant: {$variant->size})"
                        );
                    }

                    $variant->decrement('stock_quantity', $item->quantity);
                }
            }
        });
    }

}
