<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Log;

class CartPage extends Component
{
    public $cartItems;
    public $total = 0;
    public $cartCount = 0;
    public $canCheckout = true;
    public $stockIssues = [];

    protected $listeners = ['cartUpdated' => 'loadCart'];
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
            $this->canCheckout = false;
            $this->stockIssues = [];
            return;
        }

        $customer = Customer::where('user_id', Auth::id())->first();

        $this->cartItems = $customer
            ? CartItem::where('customerID', $customer->customerID)
                ->with(['product.discounts', 'variant'])
                ->get()
                ->map(function ($item) {
                    $product = $item->product;
                    $basePrice = (float) ($item->variant->price ?? $product->price);
                    
                    // Store original price
                    $item->original_price = $basePrice;

                    // Get active discount and calculate final price
                    $activeDiscount = $this->getActiveDiscount($product);
                    
                    if ($activeDiscount && $activeDiscount->discount_value > 0) {
                        $discountedPrice = $this->calculateDiscountedPrice($basePrice, $activeDiscount);
                        
                        if (($basePrice - $discountedPrice) > 0.01) {
                            $item->active_discount = $activeDiscount;
                            $item->unit_price = round($discountedPrice, 2);
                        } else {
                            $item->active_discount = null;
                            $item->unit_price = round($basePrice, 2);
                        }
                    } else {
                        $item->active_discount = null;
                        $item->unit_price = round($basePrice, 2);
                    }

                    $item->sub_total = round($item->unit_price * $item->quantity, 2);
                    return $item;
                })
            : collect();
            
        $this->recalculateTotals();
    }

    private function getActiveDiscount($product)
    {
        if (!$product || !$product->relationLoaded('discounts')) {
            return null;
        }

        return $product->discounts
            ->where('is_active', true)
            ->filter(function($discount) {
                $now = now();
                return (is_null($discount->start_date) || $discount->start_date <= $now)
                    && (is_null($discount->end_date) || $discount->end_date >= $now)
                    && $discount->discount_value > 0;
            })
            ->first();
    }

    private function calculateDiscountedPrice($basePrice, $discount)
    {
        if (!$discount || $discount->discount_value <= 0) {
            return $basePrice;
        }

        if ($discount->discount_type === 'Percentage') {
            return max($basePrice - ($basePrice * ($discount->discount_value / 100)), 0);
        } elseif ($discount->discount_type === 'Fixed') {
            return max($basePrice - $discount->discount_value, 0);
        }

        return $basePrice;
    }

    private function recalculateTotals()
    {
        $this->total = $this->cartItems->sum('sub_total');
        $this->cartCount = $this->cartItems->sum('quantity');
        $this->dispatch('cartUpdated', $this->cartCount);
    }

    public function validateStockForCheckout()
    {
        $this->stockIssues = [];
        $this->canCheckout = true;

        foreach ($this->cartItems as $item) {
            if ($item->variant) {
                $item->variant->refresh();
            } else {
                $item->product->refresh();
            }

            $warehouseStock = $item->variant 
                ? $item->variant->stock_quantity 
                : $item->product->total_stock_quantity;

            // ✅ FIX: User already has items in cart, warehouse stock is what's LEFT
            // They can't have negative warehouse stock
            if ($warehouseStock < 0) {
                $this->canCheckout = false;
                $this->stockIssues[] = [
                    'product' => $item->product->name ?? 'Unknown Product',
                    'size' => $item->size,
                    'colorway' => $item->colorway,
                    'requested' => $item->quantity,
                    'available' => $item->quantity + $warehouseStock, // What they can actually have
                ];
            }
        }
    }

    public function proceedToCheckout()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please login to proceed.');
            return redirect()->route('login');
        }

        $this->loadCart();

        if (!$this->canCheckout) {
            $this->dispatch('checkout-error', 
                message: 'Some items in your cart exceed available stock. Please update quantities before proceeding.'
            );
            return;
        }

        if ($this->cartItems->isEmpty()) {
            $this->dispatch('checkout-error', 
                message: 'Your cart is empty. Please add items before checkout.'
            );
            return;
        }

        return redirect()->route('checkout');
    }

    public function increaseQuantity($cartItemId)
    {
        $item = CartItem::with('variant')->where('cart_itemID', $cartItemId)->first();

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        // Check if we can add more based on actual stock
        if (!$item->variant) {
            session()->flash('error', 'Product variant not found.');
            return;
        }

        // Calculate total reserved quantity for this variant across all carts
        $totalReserved = CartItem::where('product_variant_id', $item->variant->id)->sum('quantity');
        $availableStock = $item->variant->stock_quantity;
        
        // Check if increasing by 1 would exceed available stock
        if (($totalReserved - $item->quantity + $item->quantity + 1) > $availableStock) {
            session()->flash('error', 'Not enough stock available.');
            return;
        }

        $this->updateQuantity($cartItemId, $item->quantity + 1);
    }

    public function decreaseQuantity($cartItemId)
    {
        $item = CartItem::with('variant')->where('cart_itemID', $cartItemId)->first();

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        if ($item->quantity <= 1) {
            $this->removeFromCart($cartItemId);
            return;
        }

        $this->updateQuantity($cartItemId, $item->quantity - 1);
    }

    private function updateQuantity($cartItemId, $newQuantity)
    {
        $item = CartItem::with(['product.discounts', 'variant'])
            ->where('cart_itemID', $cartItemId)
            ->first();

        if (!$item || !$item->variant) {
            session()->flash('error', 'Item or variant not found.');
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeFromCart($cartItemId);
            return;
        }

        // Validate against actual stock (no stock deduction, just validation)
        $totalReserved = CartItem::where('product_variant_id', $item->variant->id)
            ->where('cart_itemID', '!=', $cartItemId)
            ->sum('quantity');
        
        $availableStock = $item->variant->stock_quantity;
        
        if (($totalReserved + $newQuantity) > $availableStock) {
            session()->flash('error', 'Not enough stock available for this quantity.');
            return;
        }

        try {
            // Calculate final price with discount
            $basePrice = (float) ($item->variant->price ?? $item->product->price);
            $activeDiscount = $this->getActiveDiscount($item->product);
            $finalPrice = $this->calculateDiscountedPrice($basePrice, $activeDiscount);

            // Update ONLY cart item - DO NOT touch stock
            $item->update([
                'quantity' => $newQuantity,
                'unit_price' => round($finalPrice, 2),
                'sub_total' => round($finalPrice * $newQuantity, 2),
            ]);

            Log::info("Cart updated (no stock change)", [
                'cart_item_id' => $cartItemId,
                'new_quantity' => $newQuantity,
            ]);

            $this->loadCart();
            session()->flash('message', 'Cart updated successfully.');
        } catch (\Exception $e) {
            Log::error('Cart update failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to update cart.');
        }
    }

    public function removeFromCart($cartItemId)
    {
        $item = CartItem::where('cart_itemID', $cartItemId)->first();

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        try {
            // Just delete - DO NOT return stock
            $item->delete();

            Log::info("Item removed from cart (no stock change)", [
                'cart_item_id' => $cartItemId,
            ]);

            $this->loadCart();
            session()->flash('message', 'Item removed successfully.');
        } catch (\Exception $e) {
            Log::error('Remove from cart failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to remove item.');
        }
    }

    public function clearCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return;
        }

        try {
            // Just delete all cart items - DO NOT return stock
            CartItem::where('customerID', $customer->customerID)->delete();

            Log::info("Cart cleared (no stock change)", [
                'customer_id' => $customer->customerID,
            ]);

            $this->loadCart();
            session()->flash('message', 'Cart cleared successfully.');
        } catch (\Exception $e) {
            Log::error('Clear cart failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to clear cart.');
        }
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}