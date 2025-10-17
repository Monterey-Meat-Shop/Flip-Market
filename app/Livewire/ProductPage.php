<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

class ProductPage extends Component
{
    public $selectedCategories = [];
    public $selectedBrands = [];
    public $maxPrice = 10000;

    public function clearFilters()
    {
        $this->selectedCategories = [];
        $this->selectedBrands = [];
        $this->maxPrice = 10000;
    }

    public function render()
    {
        $products = Product::with(['brand', 'category', 'discounts'])
            ->when(count($this->selectedCategories) > 0, fn($query) =>
        $query->whereIn('CategoryID', $this->selectedCategories))
            ->when(count($this->selectedBrands) > 0, fn($query) =>
        $query->whereIn('BrandID', $this->selectedBrands))
            ->when($this->maxPrice > 0, fn($query) =>
        $query->where('price', '<=', $this->maxPrice))
            ->get();

        return view('livewire.product-page', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'products' => $products,
        ]);
    }
}
