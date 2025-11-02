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
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    protected $attributes = [
        'shipping_status' => 'Pending',
    ];

    // Define shipping method constants
    const SHIPPING_METHODS = [
        'JNT' => [
            'name' => 'J&T Express',
            'fee' => 70,
            'description' => '3-5 business days delivery',
        ],
        'LALAMOVE' => [
            'name' => 'Lalamove',
            'fee' => 120,
            'description' => 'Same day delivery',
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

    // Get shipping fee
    public function getShippingFee()
    {
        $details = $this->getShippingMethodDetails();
        return $details ? $details['fee'] : 70; // Default to 70 if method not found
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