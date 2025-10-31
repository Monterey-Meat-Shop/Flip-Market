<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

class ProductPage extends Component
{
    use WithPagination;

    public $selectedCategories = [];
    public $selectedBrands = [];
    public $maxPrice = 10000;

    protected $updatesQueryString = ['selectedCategories', 'selectedBrands', 'maxPrice'];

    // Reset pagination when filters change
    public function updatedSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatedSelectedBrands()
    {
        $this->resetPage();
    }

    public function updatedMaxPrice()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedCategories = [];
        $this->selectedBrands = [];
        $this->maxPrice = 10000;
        $this->resetPage();
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
            ->paginate(5); // ✅ Pagination added

        return view('livewire.product-page', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'products' => $products,
        ]);
    }
}
