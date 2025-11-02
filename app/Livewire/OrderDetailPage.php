<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderDetailPage extends Component
{
    public $order;
    public $orderId;
    public $productDiscountSavings = 0.0;
    public $couponDiscountAmount = 0.0;
    public $originalSubtotal = 0.0;

    public function mount(string $orderId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // customer authorization
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            session()->flash('error', 'Customer profile not found.');
            return redirect()->route('profile');
        }

        $this->orderId = $orderId;

        // load order with relationships and specific authorization
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

    private function calculateDiscounts(): void
    {
        $this->productDiscountSavings = 0.0;
        $this->originalSubtotal = 0.0;

        // discount calculation
        foreach ($this->order->orderItems as $item) {
            $unitPrice = (float) $item->unit_price;

            $originalPrice = (float) ($item->original_price ?? $unitPrice);
            $quantity = (int) $item->quantity;

            $item->discounted_unit_price = round($unitPrice, 2); 

            // Reset defaults
            $item->has_discount = false;
            $item->original_unit_price = $unitPrice;
            $item->savings_per_unit = 0.0;

            // Check if product has an assigned discount and the unit price is actually lower than original
            if (!empty($item->discount_id) && $originalPrice > $unitPrice) {
                $item->has_discount = true;
                $item->original_unit_price = $originalPrice;
                $item->savings_per_unit = round($originalPrice - $unitPrice, 2);

                $itemSavings = $item->savings_per_unit * $quantity;
                $this->productDiscountSavings += $itemSavings;
                $this->originalSubtotal += $originalPrice * $quantity;
            } else {
                // If no product discount, original subtotal is just the unit price paid
                $this->originalSubtotal += $unitPrice * $quantity;
            }
        }

        $this->productDiscountSavings = round($this->productDiscountSavings, 2);
        $this->originalSubtotal = round($this->originalSubtotal, 2);


        // discount calculation
        $this->couponDiscountAmount = 0.0;

        if ($this->order->discount) {
            // Calculate total *before* the coupon was applied (but *after* product discounts)
            $itemsTotal = (float) $this->order->orderItems->sum('sub_total');
            $shippingFee = $this->order->shipping ? (float) $this->order->shipping->shipping_fee : 0.0;
            $expectedTotal = round($itemsTotal + $shippingFee, 2);
            $finalAmount = (float) $this->order->final_amount;

            // The coupon discount is the difference between the expected total and the final amount paid.
            $calculatedDiscount = round($expectedTotal - $finalAmount, 2);

            // Set the coupon discount only if a positive amount exists
            if ($calculatedDiscount > 0.00) {
                $this->couponDiscountAmount = $calculatedDiscount;
            }
        }
    }

    /**
     * Render the Livewire view.
     */
    public function render(): View
    {
        return view('livewire.order-detail-page', [
            'productDiscountSavings' => $this->productDiscountSavings,
            'couponDiscountAmount' => $this->couponDiscountAmount,
            'originalSubtotal' => $this->originalSubtotal,
        ]);
    }
}
