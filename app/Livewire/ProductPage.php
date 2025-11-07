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

    public bool $filterSale = false;
    public bool $filterPreOrder = false;

    // search state
    public $searchInput = '';
    public $search = '';

    protected $updatesQueryString = [
        'selectedCategories',
        'selectedBrands',
        'minPriceSelected',
        'maxPriceSelected',
        'filterSale',
        'filterPreOrder',
        'search',
    ];

    public function mount()
    {
        $this->minPrice = Product::min('price') ?? 0;
        $this->maxPrice = Product::max('price') ?? 10000;

        $this->minPriceSelected = $this->minPrice;
        $this->maxPriceSelected = $this->maxPrice;

        $this->searchInput = $this->search;
    }

    // Reset pagination when filters change
    public function updatedSelectedCategories() { $this->resetPage(); }
    public function updatedSelectedBrands() { $this->resetPage(); }
    public function updatedMaxPriceSelected() { $this->resetPage(); }
    public function updatedMinPriceSelected() { $this->resetPage(); }
    public function updatedFilterSale() { $this->resetPage(); }
    public function updatedFilterPreOrder() { $this->resetPage(); }

    // Live typing: keep input but don’t commit filter until applySearch()
    public function updatedSearchInput()
    {
        // optional: live search
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    public function applySearch()
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->searchInput = '';
        $this->search = '';
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedCategories = [];
        $this->selectedBrands = [];
        $this->filterSale = false;
        $this->filterPreOrder = false;
        $this->minPriceSelected = $this->minPrice;
        $this->maxPriceSelected = $this->maxPrice;
        $this->searchInput = '';
        $this->search = '';
        $this->resetPage();
    }

    public function updatePriceRange()
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::with(['brand', 'category', 'discounts'])
            // search by name / brand / category
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $search))
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $search));
                });
            })
            ->when(count($this->selectedCategories) > 0, fn($query) =>
                $query->whereIn('categoryID', $this->selectedCategories))
            ->when(count($this->selectedBrands) > 0, fn($query) =>
                $query->whereIn('brandID', $this->selectedBrands))

            // On Sale filter
            ->when($this->filterSale, function ($query) {
                $query->whereHas('discounts', function ($q) {
                    $q->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                        });
                });
            })

            // Pre-Order filter
            ->when($this->filterPreOrder, function ($query) {
                $query->where('status', 'pre_order');
            })

            // Price range
            ->whereBetween('price', [$this->minPriceSelected, $this->maxPriceSelected])

            // ✅ 12 products per page
            ->paginate(12);

        return view('livewire.product-page', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'products' => $products,
        ]);
    }
}
