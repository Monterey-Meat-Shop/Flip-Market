<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends Model
{
    use HasFactory;

    protected $table = 'shipping';

    protected $primaryKey = 'shippingID';

    protected $fillable = [
        'orderID',
        'shipping_method',
        'shipping_status',
        'shipping_fee',
        'delivered_at',

        // ✅ NEW: Lalamove specific fields
        'lalamove_booking_option', // 'customer' or 'store'
        'lalamove_tracking',       // tracking number or URL
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'shipping_fee' => 'decimal:2',
    ];

    protected $attributes = [
        'shipping_status' => 'Pending',
    ];

    // Define shipping method constants
    const SHIPPING_METHODS = [
        'JNT' => [
            'name' => 'J&T Express',
            'fee' => 100, // fixed
            'description' => '3-5 business days delivery',
        ],

        // LALAMOVE: no fixed fee, user/admin defines
        'LALAMOVE' => [
            'name' => 'Lalamove',
            'description' => 'Same day delivery (fee based on location via Lalamove)',
        ],
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    // Get shipping method details
    public function getShippingMethodDetails()
    {
        return self::SHIPPING_METHODS[$this->shipping_method] ?? null;
    }

    // Get shipping fee (fallback only)
    public function getShippingFee()
    {
        $details = $this->getShippingMethodDetails();

        // if method has a defined fee, use it; otherwise fallback 70
        return isset($details['fee'])
            ? $details['fee']
            : 70;
    }

    // Static method to get all shipping methods
    public static function getAvailableShippingMethods()
    {
        return self::SHIPPING_METHODS;
    }

    protected static function booted()
    {
        static::updated(function ($shipping) {
            // Only trigger when the shipping status changes
            if ($shipping->isDirty('shipping_status')) {
                $user = $shipping->order?->customer?->user;

                if ($user) {
                    Notification_Customer::create([
                        'user_id'    => $user->id,
                        'orderID'    => $shipping->orderID,
                        'shippingID' => $shipping->shippingID,
                        'message'    => "Your order #{$shipping->order->orderID} shipping status is now {$shipping->shipping_status}.",
                        'is_read'    => false,
                    ]);
                }
            }
        });
    }
}
