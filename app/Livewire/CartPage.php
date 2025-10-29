<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CartPage extends Component
{
    public $cartItems;
    public $total = 0;
    public $cartCount = 0;

    protected $listeners = ['cartUpdated' => 'loadCart'];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        if (!Auth::check()) {
            $this->cartItems = collect();
            $this->total = 0;
            $this->cartCount = 0;
            return;
        }

        $customer = Customer::where('user_id', Auth::id())->first();

        $this->cartItems = $customer
            ? CartItem::where('customerID', $customer->customerID)
                ->with(['product.discounts', 'variant'])
                ->get()
                ->map(function ($item) {
                    $product = $item->product;
                    $activeDiscount = $product?->discounts
                        ?->where('status', 'active')
                        ->first();

                    // Store original price before any discounts
                    $item->original_price = $item->unit_price;

                    // Handle discount logic
                    if ($activeDiscount) {
                        $item->active_discount = $activeDiscount;

                        if ($activeDiscount->discount_type === 'Percentage') {
                            $discountAmount = ($activeDiscount->discount_value / 100) * $item->original_price;

                            $item->unit_price = $item->original_price - $discountAmount;

                        } else {
                            $newPrice = $item->original_price - $activeDiscount->discount_value;

                            $item->unit_price = max(0, $newPrice);
                        }
                    } else {
                        $item->active_discount = null;
                    }
                    // Always make sure sub_total is recalculated based on the *final* unit price
                    $item->sub_total = $item->unit_price * $item->quantity;

                    return $item;
                })
            : collect();
        $this->recalculateTotals();
    }

    public function recalculateTotals()
    {
        $this->total = $this->cartItems->sum('sub_total');
        $this->cartCount = $this->cartItems->sum('quantity');
        $this->dispatch('cartUpdated', $this->cartCount);
    }

    // Update quantity (works with + and - buttons)
    public function updateQuantity($cartItemId, $newQuantity)
    {
        $item = CartItem::find($cartItemId);

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeItem($cartItemId);
            return;
        }

        $item->quantity = $newQuantity;
        $item->sub_total = $item->unit_price * $item->quantity;
        $item->save();

        $this->loadCart();
        session()->flash('message', 'Cart updated successfully.');
    }

    // Remove a cart item
    public function removeItem($cartItemId)
    {
        $item = CartItem::find($cartItemId);

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        $item->delete();
        $this->loadCart();

        session()->flash('message', 'Item removed successfully.');
    }

    // Clear all items in the cart
    public function clearCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) return;

        CartItem::where('customerID', $customer->customerID)->delete();
        $this->loadCart();

        session()->flash('message', 'Cart cleared successfully.');
    }

    public function render()
    {
        return view('livewire.cart-page', [
            'cartItems' => $this->cartItems,
            'total' => $this->total,
            'cartCount' => $this->cartCount,
        ]);
    }
}
