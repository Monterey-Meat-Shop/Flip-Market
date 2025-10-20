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

    protected $listeners = array('cartUpdated' => 'loadCart');

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
                ->with(array('product', 'variant'))
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
        $cartItem = CartItem::with(array('product', 'variant'))->find($cartItemId);
        
        if (!$cartItem) {
            session()->flash('error', 'Cart item not found.');
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeItem($cartItemId);
            return;
        }

        // Since all products have variants (based on your model), we only work with variants
        if (!$cartItem->variant) {
            session()->flash('error', 'Product variant not found. Please refresh the page.');
            return;
        }

        DB::transaction(function () use ($cartItem, $newQuantity) {
            // Get fresh variant stock data
            $variant = $cartItem->variant->fresh();
            $currentStock = $variant->stock_quantity;

            // Calculate total available (current stock + what's currently in cart)
            $totalAvailable = $currentStock + $cartItem->quantity;

            if ($newQuantity > $totalAvailable) {
                throw new \Exception('Not enough stock available. Maximum: ' . $totalAvailable);
            }

            // Calculate stock difference
            $stockDifference = $newQuantity - $cartItem->quantity;

            // Update variant stock safely
            if ($stockDifference > 0) {
                // Taking more stock - use atomic update
                $affectedRows = DB::table('product_variants')
                    ->where('id', $cartItem->product_variant_id)
                    ->where('stock_quantity', '>=', $stockDifference)
                    ->update(array(
                        'stock_quantity' => DB::raw("stock_quantity - {$stockDifference}"),
                        'updated_at' => now()
                    ));
                    
                if ($affectedRows === 0) {
                    throw new \Exception('Insufficient stock for this update.');
                }
            } else {
                // Returning stock (stockDifference is negative)
                $returnAmount = abs($stockDifference);
                DB::table('product_variants')
                    ->where('id', $cartItem->product_variant_id)
                    ->update(array(
                        'stock_quantity' => DB::raw("stock_quantity + {$returnAmount}"),
                        'updated_at' => now()
                    ));
            }

            // Update cart item
            $unitPrice = $cartItem->variant->price ?? $cartItem->product->price;
            
            $cartItem->update(array(
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
                'sub_total' => $unitPrice * $newQuantity,
            ));
        });

        $this->loadCart();
        session()->flash('message', 'Cart updated successfully.');
    }

    public function removeItem($cartItemId)
    {
        $cartItem = CartItem::with(array('product', 'variant'))->find($cartItemId);
        
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
                ->with(array('product', 'variant'))
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
        return view('livewire.cart-page', array(
            'cartItems' => $this->cartItems,
            'total' => $this->total,
            'cartCount' => $this->cartCount,
        ));
    }
}