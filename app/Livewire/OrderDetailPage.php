<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class OrderDetailPage extends Component
{
    public $order;
    public $orderId;
    public $productDiscountSavings = 0;
    public $couponDiscountAmount = 0;
    public $originalSubtotal = 0;
    
    public function mount($orderId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$customer) {
            session()->flash('error', 'Customer profile not found.');
            return redirect()->route('profile');
        }

        $this->orderId = $orderId;

        // Find the order and verify it belongs to this customer
        $this->order = Order::where('orderID', $orderId)
            ->where('customerID', $customer->customerID)
            ->with([
                'orderItems.product.discounts', 
                'orderItems.productVariant', 
                'payment.paymentMethod', 
                'shipping', 
                'customer',
                'discount'
            ])
            ->firstOrFail();

        $this->calculateDiscounts();
    }

    /**
     * Calculate discount savings for display
     */
    private function calculateDiscounts()
    {
        $this->productDiscountSavings = 0;
        $this->originalSubtotal = 0;

        // Calculate product-level discounts
        foreach ($this->order->orderItems as $item) {
            if ($item->product) {
                // Get the current variant or product base price (without discount)
                $currentBasePrice = $item->productVariant 
                    ? ($item->productVariant->price ?? $item->product->price) 
                    : $item->product->price;

                // Check if there's an active discount on this product
                $activeDiscount = $this->getItemDiscount($item);
                
                if ($activeDiscount) {
                    // Calculate what the original price would be (current base price)
                    $originalPrice = $currentBasePrice;
                    
                    // Calculate expected discounted price
                    if ($activeDiscount->discount_type === 'Percentage') {
                        $expectedDiscountedPrice = $originalPrice - ($originalPrice * ($activeDiscount->discount_value / 100));
                    } else {
                        $expectedDiscountedPrice = $originalPrice - $activeDiscount->discount_value;
                    }
                    $expectedDiscountedPrice = max(0, $expectedDiscountedPrice);
                    
                    // If the item's unit_price is close to the expected discounted price, it was discounted
                    if (abs($item->unit_price - $expectedDiscountedPrice) < 0.01) {
                        $item->original_unit_price = $originalPrice;
                        $item->has_discount = true;
                        
                        $itemSavings = ($originalPrice - $item->unit_price) * $item->quantity;
                        $this->productDiscountSavings += $itemSavings;
                        $this->originalSubtotal += $originalPrice * $item->quantity;
                    } else {
                        // No discount was applied to this item at purchase time
                        $item->original_unit_price = $item->unit_price;
                        $item->has_discount = false;
                        $this->originalSubtotal += $item->unit_price * $item->quantity;
                    }
                } else {
                    // No active discount, compare with current base price
                    if ($item->unit_price < $currentBasePrice) {
                        // Item was purchased at a discount (discount may have expired)
                        $item->original_unit_price = $currentBasePrice;
                        $item->has_discount = true;
                        
                        $itemSavings = ($currentBasePrice - $item->unit_price) * $item->quantity;
                        $this->productDiscountSavings += $itemSavings;
                        $this->originalSubtotal += $currentBasePrice * $item->quantity;
                    } else {
                        // No discount
                        $item->original_unit_price = $item->unit_price;
                        $item->has_discount = false;
                        $this->originalSubtotal += $item->unit_price * $item->quantity;
                    }
                }
            }
        }

        // Calculate coupon/checkout-level discount
        if ($this->order->discount) {
            // The difference between order total_amount and the sum of order items
            $itemsTotal = $this->order->orderItems->sum('sub_total');
            
            // If there's a discount applied at checkout level
            $shippingFee = $this->order->shipping->shipping_fee ?? 0;
            $expectedTotal = $itemsTotal + $shippingFee;
            
            if ($this->order->final_amount < $expectedTotal) {
                $this->couponDiscountAmount = $expectedTotal - $this->order->final_amount;
            }
        }
    }

    /**
     * Get active discount info for an order item's product
     */
    private function getItemDiscount($item)
    {
        if (!$item->product || !$item->product->relationLoaded('discounts')) {
            return null;
        }

        return $item->product->discounts
            ->where('is_active', true)
            ->filter(function($discount) {
                return (is_null($discount->start_date) || $discount->start_date <= now())
                    && (is_null($discount->end_date) || $discount->end_date >= now());
            })
            ->first();
    }

    public function render()
    {
        return view('livewire.order-detail-page', [
            'productDiscountSavings' => $this->productDiscountSavings,
            'couponDiscountAmount' => $this->couponDiscountAmount,
            'originalSubtotal' => $this->originalSubtotal,
        ]);
    }
}