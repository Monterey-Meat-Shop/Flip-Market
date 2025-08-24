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
        'payment_status',
        'payment_method',
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'OrderID', 'orderID');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'orderID', 'orderID');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }
}
