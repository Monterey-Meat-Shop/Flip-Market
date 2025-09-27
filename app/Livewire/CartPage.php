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

        if ($customer) {
            $this->cartItems = CartItem::where('customerID', $customer->customerID)
                ->with(['product', 'variant'])
                ->get();
        } else {
            $this->cartItems = collect();
        }

        $this->total = $this->cartItems->sum('sub_total');
        $this->cartCount = $this->cartItems->sum('quantity');
        
        // Dispatch event to update cart count in navbar
        $this->dispatch('cartUpdated', $this->cartCount);
    }

    public function updateQuantity($cartItemId, $newQuantity)
    {
        $cartItem = CartItem::with(['product', 'variant'])->find($cartItemId);
        
        if (!$cartItem) {
            session()->flash('error', 'Cart item not found.');
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeItem($cartItemId);
            return;
        }

        // Check available stock
        $availableStock = $cartItem->variant 
            ? $cartItem->variant->stock_quantity 
            : $cartItem->product->total_stock_quantity;

        // Add back the current cart quantity to available stock for validation
        $totalAvailable = $availableStock + $cartItem->quantity;

        if ($newQuantity > $totalAvailable) {
            session()->flash('error', 'Not enough stock available. Maximum: ' . $totalAvailable);
            return;
        }

        // Calculate stock difference
        $stockDifference = $newQuantity - $cartItem->quantity;

        // Update stock in product/variant
        if ($cartItem->variant) {
            $cartItem->variant->decrement('stock_quantity', $stockDifference);
        } else {
            $cartItem->product->decrement('total_stock_quantity', $stockDifference);
        }

        // Update cart item
        $unitPrice = $cartItem->variant ? ($cartItem->variant->price ?? $cartItem->product->price) : $cartItem->product->price;
        $cartItem->update([
            'quantity' => $newQuantity,
            'unit_price' => $unitPrice,
            'sub_total' => $unitPrice * $newQuantity,
        ]);

        $this->loadCart();
        session()->flash('message', 'Cart updated successfully.');
    }

    public function removeItem($cartItemId)
    {
        $cartItem = CartItem::with(['product', 'variant'])->find($cartItemId);
        
        if (!$cartItem) {
            session()->flash('error', 'Cart item not found.');
            return;
        }

        // Return stock to product/variant
        if ($cartItem->variant) {
            $cartItem->variant->increment('stock_quantity', $cartItem->quantity);
        } else {
            $cartItem->product->increment('total_stock_quantity', $cartItem->quantity);
        }

        $cartItem->delete();
        $this->loadCart();
        session()->flash('message', 'Item removed from cart.');
    }

    public function clearCart()
    {
        if (!Auth::check()) {
            return;
        }

        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) {
            return;
        }

        // Return stock for all items
        $cartItems = CartItem::where('customerID', $customer->customerID)
            ->with(['product', 'variant'])
            ->get();

        foreach ($cartItems as $item) {
            if ($item->variant) {
                $item->variant->increment('stock_quantity', $item->quantity);
            } else {
                $item->product->increment('total_stock_quantity', $item->quantity);
            }
        }

        // Delete all cart items
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