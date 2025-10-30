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

    // Calculate product-level and coupon-level discounts
    private function calculateDiscounts()
{
    $this->productDiscountSavings = 0;
    $this->originalSubtotal = 0;

    foreach ($this->order->orderItems as $item) {
        $unitPrice = (float) $item->unit_price;
        $originalPrice = !empty($item->original_price) ? (float) $item->original_price : $unitPrice;
        $quantity = (int) $item->quantity;

        // Reset default
        $item->has_discount = false;
        $item->original_unit_price = $unitPrice;
        $item->savings_per_unit = 0;

        // Only apply product-level discount if original_price > unit_price
        if ($originalPrice > $unitPrice) {
            $item->has_discount = true;
            $item->original_unit_price = $originalPrice;
            $item->savings_per_unit = $originalPrice - $unitPrice;

            $itemSavings = $item->savings_per_unit * $quantity;
            $this->productDiscountSavings += $itemSavings;
            $this->originalSubtotal += $originalPrice * $quantity;
        } else {
            $this->originalSubtotal += $unitPrice * $quantity;
        }
    }

    // Checkout-level coupon discount
    $this->couponDiscountAmount = 0;

    if ($this->order->discount) {
        $itemsTotal = $this->order->orderItems->sum('sub_total');
        $shippingFee = $this->order->shipping ? (float) $this->order->shipping->shipping_fee : 0;
        $expectedTotal = $itemsTotal + $shippingFee;
        $finalAmount = (float) $this->order->final_amount;

        if ($finalAmount < $expectedTotal && ($expectedTotal - $finalAmount) > 0.01) {
            $this->couponDiscountAmount = $expectedTotal - $finalAmount;
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