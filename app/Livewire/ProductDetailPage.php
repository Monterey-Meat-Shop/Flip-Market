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
    public $selectedColorway = null;
    public $cartItems = [];

    public function mount($productId)
    {
        $this->product = Product::with(['category', 'brand', 'variants', 'discounts'])
            ->findOrFail($productId);

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

    public function selectColorway($color)
    {
        $this->selectedColor = $color;

        $variantQuery = $this->product->variants->where('colorway', $color);

        // Try to find a variant with the same size in the new colorway
        if ($this->selectedSize) {
            $variant = $variantQuery->firstWhere('size', $this->selectedSize);
        }

        // If no matching size found, get the first variant of this colorway
        if (!isset($variant) || !$variant) {
            $variant = $variantQuery->first();
        }

        if ($variant) {
            $this->selectedVariant = $variant;
            $this->selectedSize = $variant->size;
            
            // Update the selected image based on variant's image
            $this->updateImageForVariant($variant);
        }
    }

    /**
     * Update the selected image index based on variant's image
     */
    protected function updateImageForVariant($variant)
    {
        // Option 1: If your variant has its own image_path field
        if (!empty($variant->image_path)) {
            $images = is_array($this->product->image_url) 
                ? $this->product->image_url 
                : (json_decode($this->product->image_url, true) ?: [$this->product->image_path]);
            
            // Find the index of the variant's image in the product images array
            $index = array_search($variant->image_path, $images);
            if ($index !== false) {
                $this->selectedImage = $index;
                return;
            }
        }

        // Option 2: If images are ordered by colorway (e.g., Black=0, Brown=1, Red=2)
        // Map colorways to image indices
        $colorways = $this->product->variants->pluck('colorway')->unique()->values()->toArray();
        $colorIndex = array_search($this->selectedColor, $colorways);
        
        if ($colorIndex !== false) {
            $images = is_array($this->product->image_url) 
                ? $this->product->image_url 
                : (json_decode($this->product->image_url, true) ?: [$this->product->image_path]);
            
            // Make sure the index exists in the images array
            if (isset($images[$colorIndex])) {
                $this->selectedImage = $colorIndex;
            }
        }
    }

    /**
     * NEW METHOD: Select a size while preserving the current colorway
     */
    public function selectSize($size)
    {
        $this->selectedSize = $size;

        // Try to find a variant with both the current colorway AND selected size
        $variant = null;
        
        if ($this->selectedColor) {
            $variant = $this->product->variants
                ->where('colorway', $this->selectedColor)
                ->where('size', $size)
                ->first();
        }

        // If no variant found with current colorway, fall back to first variant with this size
        if (!$variant) {
            $variant = $this->product->variants->where('size', $size)->first();
        }

        if ($variant) {
            $this->selectedVariant = $variant;
            // Only update selectedColor if we had to fall back to a different colorway
            if ($variant->colorway !== $this->selectedColor) {
                $this->selectedColor = $variant->colorway;
            }
        }
    }

    /**
     * UPDATED: Only use this for direct variant selection (e.g., from variant ID)
     * This will update both color and size based on the variant
     */
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

        $currentlyReserved = CartItem::where('product_variant_id', $variant->id)->sum('quantity');
        $availableStock = $variant->stock_quantity;

        if (($currentlyReserved + $this->quantity) > $availableStock) {
            session()->flash('error', 'Not enough stock available.');
            return;
        }

        $originalPrice = $variant->price ?? $this->product->price;

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
            $existing = CartItem::where('customerID', $customer->customerID)
                ->where('productID', $this->product->productID)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($existing) {
                $newQty = $existing->quantity + $this->quantity;

                $otherCartsReserved = CartItem::where('product_variant_id', $variant->id)
                    ->where('cart_itemID', '!=', $existing->cart_itemID)
                    ->sum('quantity');
                
                if (($otherCartsReserved + $newQty) > $availableStock) {
                    throw new \Exception('Not enough stock available for this quantity.');
                }

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

        $existing = Favorite::where('customerID', $customer->customerID)
            ->where('productID', $this->product->productID)
            ->first();

        if ($existing) {
            $existing->delete();
            session()->flash('message', 'Removed from favorites.');
            return;
        }

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
        if ($this->selectedColor) {
            return $this->product->variants
                ->where('colorway', $this->selectedColor)
                ->pluck('size')
                ->unique()
                ->values();
        }

        return $this->product->variants
            ->pluck('size')
            ->unique()
            ->values();
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
            'colorways' => $this->getAvailableColors(),
        ]);
    }
}