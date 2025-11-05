<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Load product with relationships
        $this->product = Product::with(['category', 'brand', 'variants', 'discounts'])
            ->findOrFail($productId);

        // Default to first variant if available
        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariant = $this->product->variants->first();
            $this->selectedColor = $this->selectedVariant->colorway ?? null;
            $this->selectedSize = $this->selectedVariant->size ?? null;
        }

        // Load cart if logged in
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

        if (!$variant) {
            session()->flash('error', 'Please select a product variant.');
            return;
        }

        // Check total reserved stock across all carts + current request
        $currentlyReserved = CartItem::where('product_variant_id', $variant->id)->sum('quantity');
        $availableStock = $variant->stock_quantity;

        // Validate if we can reserve this quantity
        if (($currentlyReserved + $this->quantity) > $availableStock) {
            session()->flash('error', 'Not enough stock available.');
            return;
        }

        // Base price (from variant or product)
        $originalPrice = $variant->price ?? $this->product->price;

        // Apply discount (if active)
        $activeDiscount = $this->product->discounts
            ->where('is_active', true)
            ->filter(fn($discount) =>
                (is_null($discount->start_date) || $discount->start_date <= now()) &&
                (is_null($discount->end_date) || $discount->end_date >= now())
            )
            ->first();

        $discountAmount = 0;
        $discountName = null;
        $unitPrice = $originalPrice;

        if ($activeDiscount) {
            $discountName = $activeDiscount->discount_name ?? 'Discount';

            if ($activeDiscount->discount_type === 'Percentage') {
                $discountAmount = $originalPrice * ($activeDiscount->discount_value / 100);
            } else {
                $discountAmount = $activeDiscount->discount_value;
            }

            $unitPrice = max($originalPrice - $discountAmount, 0);
        }

        try {
            // Check existing cart item
            $existing = CartItem::where('customerID', $customer->customerID)
                ->where('productID', $this->product->productID)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($existing) {
                $newQty = $existing->quantity + $this->quantity;

                // Re-validate with existing item excluded
                $otherCartsReserved = CartItem::where('product_variant_id', $variant->id)
                    ->where('cart_itemID', '!=', $existing->cart_itemID)
                    ->sum('quantity');
                
                if (($otherCartsReserved + $newQty) > $availableStock) {
                    throw new \Exception('Not enough stock available for this quantity.');
                }

                // Update cart item - NO stock changes
                $existing->update([
                    'quantity'       => $newQty,
                    'unit_price'     => $unitPrice,
                    'original_price' => $originalPrice,
                    'discount_name'  => $discountName,
                    'discount_amount'=> $discountAmount,
                    'sub_total'      => $unitPrice * $newQty,
                ]);

                Log::info("Add to cart (existing) - NO stock change", [
                    'cart_item_id' => $existing->cart_itemID,
                    'old_quantity' => $existing->quantity - $this->quantity,
                    'new_quantity' => $newQty,
                    'added' => $this->quantity,
                ]);
            } else {
                // Create new cart item - NO stock changes
                CartItem::create([
                    'customerID'         => $customer->customerID,
                    'productID'          => $this->product->productID,
                    'product_variant_id' => $variant->id,
                    'size'               => $variant->size,
                    'colorway'           => $variant->colorway,
                    'quantity'           => $this->quantity,
                    'original_price'     => $originalPrice,
                    'unit_price'         => $unitPrice,
                    'discount_name'      => $discountName,
                    'discount_amount'    => $discountAmount,
                    'sub_total'          => $unitPrice * $this->quantity,
                ]);

                Log::info("Add to cart (new) - NO stock change", [
                    'variant_id' => $variant->id,
                    'quantity' => $this->quantity,
                    'stock_remains_at' => $availableStock,
                ]);
            }

            session()->flash('message', 'Added to cart.');

            // Refresh cart
            $this->cartItems = CartItem::with('product', 'variant')
                ->where('customerID', $customer->customerID)
                ->get();

            $this->dispatch('cartUpdated');

        } catch (\Exception $e) {
            Log::error('Add to cart failed: ' . $e->getMessage());
            session()->flash('error', $e->getMessage());
        }
    }

    public function addToFavorites()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please login to add favorites.');
            return;
        }

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            session()->flash('error', 'Please complete your customer profile first.');
            return;
        }

        // Check if this product is already favorited
        $existing = Favorite::where('customerID', $customer->customerID)
            ->where('productID', $this->product->productID)
            ->first();

        if ($existing) {
            // If already exists, remove it (toggle favorite)
            $existing->delete();
            session()->flash('message', 'Removed from favorites.');
            return;
        }

        // Otherwise, add new favorite
        Favorite::create([
            'customerID' => $customer->customerID,
            'productID' => $this->product->productID,
        ]);
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
            ->filter(fn($discount) =>
                (is_null($discount->start_date) || $discount->start_date <= now()) &&
                (is_null($discount->end_date) || $discount->end_date >= now())
            )
            ->first();

        if ($activeDiscount) {
            if ($activeDiscount->discount_type === 'Percentage') {
                return max($basePrice - ($basePrice * ($activeDiscount->discount_value / 100)), 0);
            } else {
                return max($basePrice - $activeDiscount->discount_value, 0);
            }
        }

        return $basePrice;
    }

    public function getCurrentStock()
    {
        return $this->selectedVariant
            ? $this->selectedVariant->stock_quantity
            : $this->product->total_stock_quantity;
    }

    public function render()
    {
        return view('livewire.product-detail-page', [
            'cartItems' => $this->cartItems,
        ]);
    }
}