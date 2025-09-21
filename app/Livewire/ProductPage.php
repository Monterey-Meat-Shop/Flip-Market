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
        $products = Product::query()
            ->when(!empty($this->selectedCategories), function ($query) {
                $query->whereIn('categoryID', $this->selectedCategories);
            })
            ->when(!empty($this->selectedBrands), function ($query) {
                $query->whereIn('brandID', $this->selectedBrands);
            })
            ->when(!empty($this->maxPrice) && $this->maxPrice > 0, function ($query) {
                $query->where('price', '<=', $this->maxPrice);
            })
            ->get();

        $categories = Category::all();
        $brands = Brand::all();

        return view('livewire.product-page', compact('categories', 'brands', 'products'));
    }
}