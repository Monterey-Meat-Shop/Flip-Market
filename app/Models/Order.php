<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

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
        // 'payment_status',
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

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id');
    }

    public function getFormattedShippingAddressAttribute(): string
    {
        if ($this->address_choice) {
            $address = $this->address_choice;

            if ($this->city) {
                $address .= ', ' . $this->city;
            }

            if ($this->province) {
                $address .= ', ' . $this->province;
            }

            if ($this->postal_code) {
                $address .= ' ' . $this->postal_code;
            }

            return $address;
        }
        return '— Address Not Found or Selected —';
    }

    public function returnRequest()
    {
        return $this->hasOne(ReturnRequest::class, 'orderID', 'orderID');
    }

    // Check if the order has insufficient stock
    public function hasInsufficientStock(): bool
    {
        $this->load('orderItems.productVariant', 'orderItems.product');

        foreach ($this->orderItems as $item) {
            if ($item->productVariant) {
                $availableStock = $item->productVariant->stock_quantity;
                if ($item->quantity > $availableStock) {
                    Log::warning("Insufficient stock for Order {$this->orderID}, Item: {$item->product->name}, Variant: {$item->productVariant->id}, Required: {$item->quantity}, Available: {$availableStock}");
                    return true;
                }
            } elseif ($item->product) {
                $availableStock = $item->product->total_stock_quantity;
                if ($item->quantity > $availableStock) {
                    Log::warning("Insufficient stock for Order {$this->orderID}, Product: {$item->product->name}, Required: {$item->quantity}, Available: {$availableStock}");
                    return true;
                }
            }
        }

        return false;
    }

    // Auto-reject the order if it has insufficient stock
    public function autoRejectIfOutOfStock(): bool
    {
        if ($this->order_status !== 'pending') {
            return false; // Only check pending orders
        }

        if ($this->hasInsufficientStock()) {
            Log::info("Auto-rejecting Order {$this->orderID} due to insufficient stock.");
            
            // Restock products if they were already deducted
            $this->restockProducts();
            
            // Update order status
            $this->update([
                'order_status' => 'cancelled',
            ]);

            Log::info("Order {$this->orderID} auto-rejected and products restocked.");
            
            return true; // Order was rejected
        }

        return false; // Order is fine
    }

    // Restock the products that were already deducted
    public function restockProducts(): void
    {
        if (! $this->stock_deducted) {
            Log::info("Order {$this->orderID} stock not deducted — skipping restock.");
            return;
        }

        $this->load('orderItems.productVariant', 'orderItems.product');

        foreach ($this->orderItems as $item) {
            if ($item->productVariant) {
                $item->productVariant->increment('stock_quantity', $item->quantity);

                if (method_exists($item->productVariant->product, 'refreshProductStatus')) {
                    $item->productVariant->product->refreshProductStatus();
                }
            } elseif ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);

                if (method_exists($item->product, 'refreshProductStatus')) {
                    $item->product->refreshProductStatus();
                }
            }
        }

        $this->update(['stock_deducted' => false]);

        Log::info("Order {$this->orderID} restocked successfully.");
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

        $this->stock_deducted = true;
        $this->saveQuietly();
    }
    
    public function getOverallDiscountAttribute()
    {
        return $this->orderItems()->sum('discount_amount');
    }

    // this is how notification will be created 
    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->isDirty('order_status')) {
                $user = $order->customer?->user;

                if ($user) {
                    Notification_Customer::create([
                        'user_id'    => $user->id,
                        'orderID'    => $order->orderID,  // link notification to order
                        'shippingID' => $order->shipping?->shippingID ?? null, // optional if exists
                        'message'    => "Your order #{$order->orderID} status has been updated to {$order->order_status}.",
                        'is_read'    => false,
                    ]);
                }
            }
        });
    }
}