<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

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

    // 🔍 Search text
    public $search = '';

    // Keep your existing query string behavior + add search
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
    }

    // Reset pagination when filters change
    public function updatedSelectedCategories() { $this->resetPage(); }
    public function updatedSelectedBrands() { $this->resetPage(); }
    public function updatedMaxPriceSelected() { $this->resetPage(); }
    public function updatedMinPriceSelected() { $this->resetPage(); }
    public function updatedFilterSale() { $this->resetPage(); }
    public function updatedFilterPreOrder() { $this->resetPage(); }

    // 🔍 Reset page when search input changes
    public function updatedSearch()
    {
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

            // 🔍 SEARCH: match starting with typed text (name / brand / category)
            ->when(trim($this->search) !== '', function (Builder $query) {
                $search = mb_strtolower(trim($this->search)) . '%';

                $query->where(function (Builder $q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$search])
                      ->orWhereHas('brand', function (Builder $b) use ($search) {
                          $b->whereRaw('LOWER(name) LIKE ?', [$search]);
                      })
                      ->orWhereHas('category', function (Builder $c) use ($search) {
                          $c->whereRaw('LOWER(name) LIKE ?', [$search]);
                      });
                });
            })

            // Category filter
            ->when(count($this->selectedCategories) > 0, function (Builder $query) {
                return $query->whereIn('categoryID', $this->selectedCategories);
            })

            // Brand filter
            ->when(count($this->selectedBrands) > 0, function (Builder $query) {
                return $query->whereIn('brandID', $this->selectedBrands);
            })

            // On Sale filter
            ->when($this->filterSale, function (Builder $query) {
                $query->whereHas('discounts', function ($q) {
                    $q->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('start_date')
                              ->orWhere('start_date', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('end_date')
                              ->orWhere('end_date', '>=', now());
                        });
                });
            })

            // Pre-Order filter
            ->when($this->filterPreOrder, function (Builder $query) {
                $query->where('status', 'pre_order');
            })

            // Price range
            ->whereBetween('price', [$this->minPriceSelected, $this->maxPriceSelected])

            ->paginate(10);

        return view('livewire.product-page', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'products' => $products,
        ]);
    }
}
