<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'paymentID';

    protected $fillable = [
        'orderID',
        'payment_methodID',
        'amount',
        'reference_number',
        'status',
    ];

    /**
     * Get the order that this payment belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'orderID');
    }

    /**
     * Get the payment method used for this payment.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_methodID');
    }
}
