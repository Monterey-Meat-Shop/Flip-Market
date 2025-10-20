<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartPage extends Component
{
    public $cartItems;
    public $total = 0;
    public $cartCount = 0;
    public $canCheckout = true;
    public $stockIssues = [];

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

        if ($customer) {
            $this->cartItems = CartItem::where('customerID', $customer->customerID)
                ->with(['product', 'variant'])
                ->get();
        } else {
            $this->cartItems = collect();
        }

        $this->total = $this->cartItems->sum('sub_total');
        $this->cartCount = $this->cartItems->sum('quantity');
        
        $this->validateStockForCheckout();
        
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

    public function updateQuantity($cartItemId, $newQuantity)
    {
        $cartItem = CartItem::with(['product', 'variant'])->find($cartItemId);

        if (!$cartItem) {
            $this->dispatch('stock-limit', message: 'Cart item not found.');
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeItem($cartItemId);
            return;
        }

        $variant = $cartItem->variant?->fresh();

        if (!$variant) {
            $this->dispatch('stock-limit', message: 'Product variant not found.');
            return;
        }

        // ✅ CRITICAL FIX: Correct stock calculation
        // When user has 10 items in cart and warehouse has 22:
        // - They're trying to change to $newQuantity
        // - Current cart has $currentCartQuantity (10)
        // - Warehouse has $warehouseStock (22)
        // - Max they can have = current + warehouse = 10 + 22 = 32
        
        $currentCartQuantity = $cartItem->quantity;
        $warehouseStock = $variant->stock_quantity;
        
        // Calculate the change (positive = adding, negative = removing)
        $quantityChange = $newQuantity - $currentCartQuantity;

        // ✅ FIX: Check if warehouse has enough for the ADDITIONAL items needed
        if ($quantityChange > 0 && $quantityChange > $warehouseStock) {
            $maxPossible = $currentCartQuantity + $warehouseStock;
            $this->dispatch('stock-limit', 
                message: "Only {$warehouseStock} more items available in stock. Maximum you can have is {$maxPossible}."
            );
            $this->loadCart();
            return;
        }

        Log::info('UpdateQuantity Debug', [
            'cart_item_id' => $cartItemId,
            'current_cart_qty' => $currentCartQuantity,
            'new_quantity' => $newQuantity,
            'quantity_change' => $quantityChange,
            'warehouse_stock' => $warehouseStock,
        ]);

        try {
            DB::transaction(function () use ($cartItem, $variant, $quantityChange, $newQuantity) {
                if ($quantityChange > 0) {
                    // User wants MORE items - deduct from warehouse
                    $variant->decrement('stock_quantity', $quantityChange);
                    
                    Log::info('Stock decremented', [
                        'amount' => $quantityChange,
                        'warehouse_after' => $variant->fresh()->stock_quantity
                    ]);
                    
                } elseif ($quantityChange < 0) {
                    // User wants FEWER items - return to warehouse
                    $returnAmount = abs($quantityChange);
                    $variant->increment('stock_quantity', $returnAmount);
                    
                    Log::info('Stock returned', [
                        'amount' => $returnAmount,
                        'warehouse_after' => $variant->fresh()->stock_quantity
                    ]);
                }

                // Update cart item
                $unitPrice = $variant->price ?? $cartItem->product->price;

                $cartItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                    'sub_total' => $unitPrice * $newQuantity,
                ]);

                Log::info('Cart updated', [
                    'new_cart_quantity' => $newQuantity,
                    'subtotal' => $unitPrice * $newQuantity
                ]);
            });

            $this->loadCart();
            $this->dispatch('cart-updated');
            
        } catch (\Exception $e) {
            Log::error('UpdateQuantity failed', [
                'error' => $e->getMessage(),
                'cart_item_id' => $cartItemId
            ]);
            
            $this->dispatch('stock-limit', 
                message: 'Unable to update quantity. ' . $e->getMessage()
            );
            $this->loadCart();
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
            // Return ALL cart quantity back to warehouse
            if ($cartItem->variant) {
                $cartItem->variant->increment('stock_quantity', $cartItem->quantity);
                
                Log::info('Item removed, stock returned', [
                    'variant_id' => $cartItem->variant->variantID,
                    'returned_quantity' => $cartItem->quantity,
                    'warehouse_after' => $cartItem->variant->fresh()->stock_quantity
                ]);
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
            $cartItems = CartItem::where('customerID', $customer->customerID)
                ->with(['product', 'variant'])
                ->get();

            // Return ALL items back to warehouse
            foreach ($cartItems as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                } else {
                    $item->product->increment('total_stock_quantity', $item->quantity);
                }
            }

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
            'canCheckout' => $this->canCheckout,
            'stockIssues' => $this->stockIssues,
        ]);
    }
}