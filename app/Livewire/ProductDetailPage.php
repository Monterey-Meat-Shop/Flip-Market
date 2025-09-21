<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductDetailPage extends Component
{
    public $product;
    public $selectedVariant;
    public $quantity = 1;
    public $selectedImage = 0;
    public $selectedColor = null;
    public $selectedSize = null;
    public $shippingOption = 'shipping';

    public function mount($productId)
    {
        $this->product = Product::with(['category', 'brand', 'variants'])->findOrFail($productId);
        
        // Set default variant if available
        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariant = $this->product->variants->first();
            $this->selectedColor = $this->selectedVariant->colorway ?? null; // Using colorway
            $this->selectedSize = $this->selectedVariant->size ?? null;
        }
    }

    public function selectVariant($variantId)
    {
        $this->selectedVariant = $this->product->variants->find($variantId);
        if ($this->selectedVariant) {
            $this->selectedColor = $this->selectedVariant->colorway; // Using colorway
            $this->selectedSize = $this->selectedVariant->size;
        }
    }

    public function selectImage($index)
    {
        $this->selectedImage = $index;
    }

    public function addToCart()
    {
        // Add your cart logic here
        session()->flash('message', 'Product added to cart successfully!');
    }

    public function addToFavorites()
    {
        // Add your favorites logic here
        session()->flash('message', 'Product added to favorites!');
    }

    public function getAvailableColors()
    {
        return $this->product->variants->pluck('colorway')->unique()->filter(); // Using colorway
    }

    public function getAvailableSizes()
    {
        return $this->product->variants->pluck('size')->unique()->filter();
    }

    public function getCurrentPrice()
    {
        return $this->selectedVariant ? $this->selectedVariant->price : $this->product->price;
    }

    public function getCurrentStock()
    {
        return $this->selectedVariant ? $this->selectedVariant->stock_quantity : $this->product->total_stock_quantity;
    }

    public function render()
    {
        return view('livewire.product-detail-page');
    }
}