<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class ProductDetailPage extends Component
{
    public $product;
    public $selectedVariant;
    public $quantity = 1;
    public $selectedImage = 0;
    public $selectedColor = null;
    public $selectedSize = null;
    public $shippingOption = 'shipping';
    public $cartItems = [];

    public function mount($productId)
    {
        $this->product = Product::with(['category', 'brand', 'variants', 'discounts'])->findOrFail($productId);
        
        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariant = $this->product->variants->first();
            $this->selectedColor = $this->selectedVariant->colorway ?? null;
            $this->selectedSize = $this->selectedVariant->size ?? null;
        }

        if (Auth::check()) {
            $customer = Customer::where('user_id', Auth::id())->first();
            if ($customer) {
                $this->cartItems = CartItem::with('product', 'variant')
                    ->where('customerID', $customer->customerID)
                    ->get();
            }
        }
    }

    public function selectVariant($variantId)
    {
        $this->selectedVariant = $this->product->variants->find($variantId);
        if ($this->selectedVariant) {
            $this->selectedColor = $this->selectedVariant->colorway;
            $this->selectedSize = $this->selectedVariant->size;
        }
    }

    public function selectImage($index)
    {
        $this->selectedImage = $index;
    }

    public function addToCart()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please login to add items to cart.');
            return;
        }

        $userId = Auth::id();
        $customer = Customer::where('user_id', $userId)->first();

        if (!$customer) {
            session()->flash('error', 'Please complete your customer profile before adding to cart.');
            return;
        }

        $variant = $this->selectedVariant;
        $availableStock = $variant ? $variant->stock_quantity : $this->product->total_stock_quantity;

        if ($availableStock < $this->quantity) {
            session()->flash('error', 'Not enough stock for selected quantity.');
            return;
        }

        // Get base price
        $unitPrice = $variant ? ($variant->price ?? $this->product->price) : $this->product->price;

        // Apply discount if available
        $discount = $this->product->discount;
        if ($discount) {
            if ($discount->discount_type === 'Percentage') {
                $unitPrice -= $unitPrice * ($discount->amount / 100);
            } else {
                $unitPrice -= $discount->amount;
            }
            $unitPrice = max($unitPrice, 0); // prevent negative price
        }

        $existing = CartItem::where('customerID', $customer->customerID)
            ->where('productID', $this->product->productID)
            ->when($variant, fn($q) => $q->where('product_variant_id', $variant->id))
            ->first();

        if ($existing) {
            $newQty = $existing->quantity + $this->quantity;
            if ($newQty > $availableStock) {
                session()->flash('error', 'Cannot add that many items. Not enough stock.');
                return;
            }
            $existing->update([
                'quantity'  => $newQty,
                'sub_total' => $unitPrice * $newQty,
            ]);
        } else {
            CartItem::create([
                'customerID'        => $customer->customerID,
                'productID'         => $this->product->productID,
                'product_variant_id'=> $variant ? $variant->id : null,
                'size'              => $variant ? $variant->size : null,
                'colorway'          => $variant ? $variant->colorway : null,
                'quantity'          => $this->quantity,
                'unit_price'        => $unitPrice,
                'sub_total'         => $unitPrice * $this->quantity,
            ]);
        }

        session()->flash('message', 'Added to cart.');

        $this->cartItems = CartItem::with('product', 'variant')
            ->where('customerID', $customer->customerID)
            ->get();

        $this->dispatch('cartUpdated');
    }

    public function addToFavorites()
    {
        session()->flash('message', 'Product added to favorites!');
    }

    public function getAvailableColors()
    {
        return $this->product->variants->pluck('colorway')->unique()->filter();
    }

    public function getAvailableSizes()
    {
        return $this->product->variants->pluck('size')->unique()->filter();
    }

    public function getCurrentPrice()
    {
        $basePrice = $this->selectedVariant 
            ? ($this->selectedVariant->price ?? $this->product->price) 
            : $this->product->price;
    
        $activeDiscount = $this->product->discounts
            ->where('is_active', true)
            ->filter(function($discount) {
                return (is_null($discount->start_date) || $discount->start_date <= now())
                    && (is_null($discount->end_date) || $discount->end_date >= now());
            })
            ->first();

        if ($activeDiscount) {
            return $activeDiscount->getFinalPrice($basePrice);
        }

        return $basePrice;
    }

    public function getCurrentStock()
    {
        return $this->selectedVariant ? $this->selectedVariant->stock_quantity : $this->product->total_stock_quantity;
    }

    public function render()
    {
        return view('livewire.product-detail-page', [
            'cartItems' => $this->cartItems,
        ]);
    }
}
