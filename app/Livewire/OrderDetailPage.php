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
                'orderItems.product', 
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

        // Calculate product-level discounts from OrderItem data
        foreach ($this->order->orderItems as $item) {
            // Check if the order item has discount information stored
            if (!empty($item->original_price) && $item->original_price > $item->unit_price) {
                // Discount was applied - use stored data
                $item->has_discount = true;
                $item->original_unit_price = $item->original_price;
                $item->savings_per_unit = $item->original_price - $item->unit_price;
                
                $itemSavings = ($item->original_price - $item->unit_price) * $item->quantity;
                $this->productDiscountSavings += $itemSavings;
                $this->originalSubtotal += $item->original_price * $item->quantity;
            } 
            // Fallback: Check if discount_amount is stored
            elseif (!empty($item->discount_amount) && $item->discount_amount > 0) {
                $item->has_discount = true;
                $item->original_unit_price = $item->unit_price + $item->discount_amount;
                $item->savings_per_unit = $item->discount_amount;
                
                $itemSavings = $item->discount_amount * $item->quantity;
                $this->productDiscountSavings += $itemSavings;
                $this->originalSubtotal += $item->original_unit_price * $item->quantity;
            }
            // No discount information stored in order item
            else {
                $item->has_discount = false;
                $item->original_unit_price = $item->unit_price;
                $item->savings_per_unit = 0;
                $this->originalSubtotal += $item->unit_price * $item->quantity;
            }
        }

        // Calculate coupon/order-level discount
        if ($this->order->discount) {
            // Method 1: Direct calculation from total_amount and final_amount
            $itemsTotal = $this->order->orderItems->sum('sub_total');
            $shippingFee = $this->order->shipping->shipping_fee ?? 0;
            
            // Expected total before order-level discount
            $expectedTotal = $itemsTotal + $shippingFee;
            
            // The difference is the coupon discount
            if ($this->order->final_amount < $expectedTotal) {
                $this->couponDiscountAmount = $expectedTotal - $this->order->final_amount;
            }
            
            // Alternative: Calculate based on discount rules (if available)
            // This is useful if final_amount calculation is complex
            if ($this->couponDiscountAmount == 0 && $this->order->total_amount > $this->order->final_amount) {
                $totalBeforeShipping = $this->order->total_amount;
                $totalWithShipping = $totalBeforeShipping + $shippingFee;
                
                if ($this->order->discount->discount_type === 'Percentage') {
                    $potentialDiscount = $totalBeforeShipping * ($this->order->discount->discount_value / 100);
                } else {
                    $potentialDiscount = $this->order->discount->discount_value;
                }
                
                // Check if this matches
                if (abs(($totalWithShipping - $potentialDiscount) - $this->order->final_amount) < 0.01) {
                    $this->couponDiscountAmount = $potentialDiscount;
                }
            }
        }
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