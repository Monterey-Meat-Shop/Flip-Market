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
public $minPriceSelected;
public $maxPriceSelected;
public $minPrice;
public $maxPrice;

    protected $updatesQueryString = ['selectedCategories', 'selectedBrands', 'minPriceSelected', 'maxPriceSelected'];

     public function mount()
{
    $this->minPrice = Product::min('price') ?? 1000;
    $this->maxPrice = Product::max('price') ?? 10000;

    $this->minPriceSelected = $this->minPrice;
    $this->maxPriceSelected = $this->maxPrice;
}


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
        $this->resetPage();
    }

    public function updatePriceRange()
{
    $this->resetPage(); // Reset pagination
}

    public function render()
    {
        $products = Product::with(['brand', 'category', 'discounts'])
            ->when(count($this->selectedCategories) > 0, fn($query) =>
                $query->whereIn('CategoryID', $this->selectedCategories))
            ->when(count($this->selectedBrands) > 0, fn($query) =>
                $query->whereIn('BrandID', $this->selectedBrands))
            ->whereBetween('price', [$this->minPriceSelected, $this->maxPriceSelected])
            ->paginate(10); // ✅ Pagination added




        return view('livewire.product-page', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'products' => $products,
        ]);
    }
}
