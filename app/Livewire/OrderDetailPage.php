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
    // Public properties mapped to the view
    public $order;
    public $orderId;
    public $productDiscountSavings = 0.0;
    public $couponDiscountAmount = 0.0;
    public $originalSubtotal = 0.0;

    /**
     * Mount the component and load the order data.
     * * NOTE: The explicit return type hint (e.g., : RedirectResponse|null) 
     * has been removed to avoid "void can only be used as a standalone type" 
     * errors in environments running older PHP versions (pre 8.1).
     *
     * @param string $orderId The ID of the order to load.
     * @return RedirectResponse|null
     */
    public function mount(string $orderId) // <-- Type hint removed here
    {
        // 1. Authentication Check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Customer Authorization Check
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            session()->flash('error', 'Customer profile not found.');
            return redirect()->route('profile');
        }

        $this->orderId = $orderId;

        // 3. Load order with relationships and specific authorization
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
        
        // No explicit return is needed here, Livewire handles the flow.
    }

    /**
     * Calculate product-level and coupon-level discounts.
     * Uses rounding to handle floating-point arithmetic for currency.
     */
    private function calculateDiscounts(): void
    {
        $this->productDiscountSavings = 0.0;
        $this->originalSubtotal = 0.0;

        // --- 1. Product-Level Discount Calculation ---
        foreach ($this->order->orderItems as $item) {
            $unitPrice = (float) $item->unit_price;
            // Use null-coalescing and ensure we cast to float for safety
            $originalPrice = (float) ($item->original_price ?? $unitPrice);
            $quantity = (int) $item->quantity;

            // --- NEW: This is the price the customer *actually paid* per unit ---
            $item->discounted_unit_price = round($unitPrice, 2); 
            // ------------------------------------------------------------------

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


        // --- 2. Checkout-Level Coupon Discount Calculation ---
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
