<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Helper method to get active discount for a product
     */
    private function getActiveDiscount($product)
    {
        if (!$product || !$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        return $product->discounts
            ->where('is_active', true)
            ->filter(function($discount) {
                return (is_null($discount->start_date) || $discount->start_date <= now())
                    && (is_null($discount->end_date) || $discount->end_date >= now());
            })
            ->first();
    }

    /**
     * Calculate discounted price
     */
    private function calculateDiscountedPrice($basePrice, $discount)
    {
        if (!$discount) {
            return $basePrice;
        }

        if ($discount->discount_type === 'Percentage') {
            $discountedPrice = $basePrice - ($basePrice * ($discount->discount_value / 100));
        } else {
            $discountedPrice = $basePrice - $discount->discount_value;
        }

        return max($discountedPrice, 0); // prevent negative price
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
            // Eager load relationships including discounts
            $this->cartItems = CartItem::where('customerID', $customer->customerID)
                ->with(['product.discounts', 'variant'])
                ->get();
        } else {
            $this->cartItems = collect();
        }

        // Calculate discounted prices for each item
        foreach ($this->cartItems as $item) {
            $basePrice = $item->variant->price ?? $item->product->price;
            $activeDiscount = $this->getActiveDiscount($item->product);
            
            $discountedPrice = $this->calculateDiscountedPrice($basePrice, $activeDiscount);

            // Update the item with discounted price
            $item->unit_price = $discountedPrice;
            $item->sub_total = $discountedPrice * $item->quantity;
            
            // Store discount info for display (optional)
            $item->active_discount = $activeDiscount;
            $item->original_price = $basePrice;
        }

        $this->total = $this->cartItems->sum('sub_total');
        $this->cartCount = $this->cartItems->sum('quantity');

        // Update cart count in navbar
        $this->dispatch('cartUpdated', $this->cartCount);
    }

    public function updateQuantity($cartItemId, $newQuantity)
    {
        $cartItem = CartItem::with(['product.discounts', 'variant'])->find($cartItemId);
        
        if (!$cartItem) {
            session()->flash('error', 'Cart item not found.');
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeItem($cartItemId);
            return;
        }

        try {
            DB::transaction(function () use ($cartItem, $newQuantity) {
                $variant = $cartItem->variant->fresh();
                $currentStock = $variant->stock_quantity;
                $totalAvailable = $currentStock + $cartItem->quantity;

                if ($newQuantity > $totalAvailable) {
                    throw new \Exception('Not enough stock available. Maximum: ' . $totalAvailable);
                }

                $stockDifference = $newQuantity - $cartItem->quantity;

                if ($stockDifference > 0) {
                    $affectedRows = DB::table('product_variants')
                        ->where('id', $cartItem->product_variant_id)
                        ->where('stock_quantity', '>=', $stockDifference)
                        ->update([
                            'stock_quantity' => DB::raw("stock_quantity - {$stockDifference}"),
                            'updated_at' => now()
                        ]);

                    if ($affectedRows === 0) {
                        throw new \Exception('Insufficient stock for this update.');
                    }
                } else {
                    $returnAmount = abs($stockDifference);
                    DB::table('product_variants')
                        ->where('id', $cartItem->product_variant_id)
                        ->update([
                            'stock_quantity' => DB::raw("stock_quantity + {$returnAmount}"),
                            'updated_at' => now()
                        ]);
                }

                // Recalculate unit price with discount
                $unitPrice = $cartItem->variant->price ?? $cartItem->product->price;
                $activeDiscount = $this->getActiveDiscount($cartItem->product);
                
                $discountedPrice = $this->calculateDiscountedPrice($unitPrice, $activeDiscount);

                $cartItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $discountedPrice,
                    'sub_total' => $discountedPrice * $newQuantity,
                ]);
            });

            $this->loadCart();
            session()->flash('message', 'Cart updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeItem($cartItemId)
    {
        $cartItem = CartItem::with(['product', 'variant'])->find($cartItemId);
        
        if (!$cartItem) {
            session()->flash('error', 'Cart item not found.');
            return;
        }

        DB::transaction(function () use ($cartItem) {
            // Return stock to product/variant
            if ($cartItem->variant) {
                $cartItem->variant->increment('stock_quantity', $cartItem->quantity);
            } else {
                $cartItem->product->increment('total_stock_quantity', $cartItem->quantity);
            }

            $cartItem->delete();
        });

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

        DB::transaction(function () use ($customer) {
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
        });
        
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