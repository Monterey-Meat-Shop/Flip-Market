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

        // Load order with relationships
        $this->order = Order::where('orderID', $orderId)
            ->where('customerID', $customer->customerID)
            ->with([
                'orderItems.product',
                'orderItems.productVariant',
                'payment.paymentMethod',
                'shipping',
                'customer',
                'discount',
            ])
            ->firstOrFail();

        $this->calculateDiscounts();
    }

    /**
     * Calculate product-level and coupon-level discounts.
     */
    private function calculateDiscounts()
{
    $this->productDiscountSavings = 0;
    $this->originalSubtotal = 0;

    foreach ($this->order->orderItems as $item) {
        // Case 1: Has original price stored (primary check)
        if (!empty($item->original_price) && $item->original_price > $item->unit_price) {
            $item->has_discount = true;
            $item->original_unit_price = $item->original_price;
            $item->savings_per_unit = $item->original_price - $item->unit_price;

            $itemSavings = $item->savings_per_unit * $item->quantity;
            $this->productDiscountSavings += $itemSavings;
            $this->originalSubtotal += $item->original_price * $item->quantity;
        }
        // Case 2: Has discount_amount but no original_price
        elseif (!empty($item->discount_amount) && $item->discount_amount > 0) {
            $item->has_discount = true;
            $item->original_unit_price = $item->unit_price + $item->discount_amount;
            $item->savings_per_unit = $item->discount_amount;
            
            // Set original_price for blade access
            $item->original_price = $item->original_unit_price;

            $itemSavings = $item->discount_amount * $item->quantity;
            $this->productDiscountSavings += $itemSavings;
            $this->originalSubtotal += $item->original_unit_price * $item->quantity;
        }
        // Case 3: No discount
        else {
            $item->has_discount = false;
            $item->original_unit_price = $item->unit_price;
            $item->savings_per_unit = 0;
            $this->originalSubtotal += $item->unit_price * $item->quantity;
        }
    }

    // Calculate coupon discount
    if ($this->order->discount) {
        $itemsTotal = $this->order->orderItems->sum('sub_total');
        $shippingFee = $this->order->shipping->shipping_fee ?? 0;
        $expectedTotal = $itemsTotal + $shippingFee;

        if ($this->order->final_amount < $expectedTotal) {
            $this->couponDiscountAmount = $expectedTotal - $this->order->final_amount;
        } else {
            $this->couponDiscountAmount = 0;
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
