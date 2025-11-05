<div>
    <div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen pt-10 pb-20">
        <h1 class="text-center text-3xl font-extrabold text-gray-900 mb-10 tracking-tight">All Products</h1>

        <section class="py-12 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="mx-auto max-w-7xl px-6 grid grid-cols-1 lg:grid-cols-5 gap-8">

                <!-- Filter Sidebar -->
                <aside class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-4 shadow-2xl lg:col-span-1 h-fit sticky top-4 border border-gray-700">
                    <h2 class="text-lg font-bold text-white mb-4 tracking-wide">Filters</h2>

                <!-- Price Range -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Price Range</h3>
        
                    <div class="flex flex-col space-x-2">
                        <div class="flex items-center justify-between">
                            <label class="text-gray-700">Low</label>
                            <label class="text-gray-700">High</label>
                        </div>
                        <div class="flex gap-2">
                            <input type="number" 
                                min="{{ $minPrice }}" 
                                max="{{ $maxPriceSelected }}" 
                                wire:model.lazy="minPriceSelected" 
                                wire:keydown.enter="updatePriceRange" 
                                class="w-1/2 border rounded px-2 py-1" 
                                placeholder="Min price"> 
                            <label class="text-gray-700">-</label>
                            <input type="number"
                                min="{{ $minPriceSelected }}" 
                                max="{{ $maxPrice }}" 
                                wire:model.lazy="maxPriceSelected" 
                                wire:keydown.enter="updatePriceRange" 
                                class="w-1/2 border rounded px-2 py-1" 
                                placeholder="Max price">
                        </div>
                    </div>
        
                    <p class="text-sm text-gray-500 mt-1">
                        Showing products between ₱{{ number_format($minPriceSelected) }} and ₱{{ number_format($maxPriceSelected) }}
                    </p>
                </div>

                <!-- Sale and Pre-Order Filter -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Availability</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li>
                            <input 
                                type="checkbox"
                                id="on-sale"
                                wire:model.live="filterSale"
                                wire:change="$refresh"
                                class="mr-2 cursor-pointer"
                            >
                            <label for="on-sale" class="cursor-pointer">On Sale</label>
                        </li>
                        <li>
                            <input 
                                type="checkbox"
                                id="pre-order"
                                wire:model.live="filterPreOrder"
                                wire:change="$refresh"
                                class="mr-2 cursor-pointer"
                            >
                            <label for="pre-order" class="cursor-pointer">Pre-Order</label>
                        </li>
                    </ul>
                </div>

                <!-- Category Filter -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Category</h3>
                    <ul class="space-y-2 text-gray-700">
                        @foreach($categories as $category)
                            <li wire:key="category-{{ $category->CategoryID }}">
                                <input type="checkbox"
                                    id="category-{{ $category->CategoryID }}"
                                    wire:model.live="selectedCategories"
                                    value="{{ $category->CategoryID }}"
                                    class="mr-2">
                                <label for="category-{{ $category->CategoryID }}">{{ $category->name }}</label>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Brand Filter -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Brand</h3>
                    <ul class="space-y-2 text-gray-700">
                        @foreach($brands as $brand)
                            <li wire:key="brand-{{ $brand->BrandID }}">
                                <input type="checkbox"
                                    id="brand-{{ $brand->BrandID }}"
                                    wire:model.live="selectedBrands"
                                    value="{{ $brand->BrandID }}"
                                    class="mr-2">
                                <label for="brand-{{ $brand->BrandID }}">{{ $brand->name }}</label>
                            </li>
                        @endforeach
                    </ul>
                </div>

                    <!-- Clear Filters Button -->
                    <button 
                        wire:click="clearFilters" 
                        class="w-full bg-gradient-to-r from-gray-200 to-gray-300 hover:from-gray-300 hover:to-gray-400 text-gray-800 font-semibold py-2 px-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 text-sm"
                    >
                        Clear All Filters
                    </button>
                </aside>

                <!-- Product List -->
                <div class="lg:col-span-4">
                    <!-- Results Count -->
                    <div class="mb-6">
                        <p class="text-gray-700 font-medium text-base">Showing {{ $products->count() }} products</p>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse($products as $product)
                            <article class="rounded-xl bg-white shadow-lg hover:shadow-xl duration-300 overflow-hidden border border-gray-200 transform hover:scale-105 transition-all">
                                <!-- Product Link (wraps image and content) -->
                                <a href="{{ route('product.detail', $product->productID) }}" class="block">
                                    <div class="relative overflow-hidden group">
                                        <img 
    src="{{ image_url($product->image_path) }}" 
    alt="{{ $product->name }}" 
    class="w-full h-40 object-cover group-hover:scale-110 transition-transform duration-300"
/>

                                        <!-- Optional: Add brand badge -->
                                        @if($product->brand)
                                            <span class="absolute top-2 left-2 bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded-full font-semibold shadow-md">
                                                {{ $product->brand->name }}
                                            </span>
                                        @endif

                                    @if($product->discounted_price < $product->price)
                                        <span class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow-md">
                                            SALE
                                        </span>
                                    @endif

                                    @if(strtolower($product->status) === 'pre_order')
                                        <span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded shadow-md">
                                            PRE-ORDER
                                        </span>
                                    @endif

                                    @if($product->total_stock_quantity <= 0)
                                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                            Out of Stock
                                        </span>
                                    @endif
                                </div>

                                    <div class="p-4 flex flex-col justify-between h-28">
                                        <div>
                                            <h2 class="text-slate-800 font-bold text-lg mb-1 leading-tight">{{ $product->name }}</h2>
                                            <p class="text-xs text-slate-500 mb-2 font-medium">
                                                {{ $product->category->name ?? 'No Category' }}
                                            </p>

                                            @if($product->discounted_price < $product->price)
                                                <div class="flex items-center space-x-1">
                                                    <p class="text-xs text-gray-500 line-through leading-tight">₱{{ number_format($product->price, 2) }}</p>
                                                    <p class="text-lg font-bold text-red-600 leading-tight">
                                                        ₱{{ number_format($product->discounted_price, 2) }}
                                                    </p>
                                                </div>
                                            @else
                                                <p class="text-lg font-bold text-blue-600 leading-tight">
                                                    ₱{{ number_format($product->price, 2) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                
                                <div class="p-4 pt-0">
                                    <button onclick="checkLoginAndAddToCart({{ $product->productID }})"
                                            @if($product->total_stock_quantity <= 0) disabled @endif
                                            class="w-full flex items-center justify-center space-x-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 px-3 py-2 text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 disabled:bg-gray-400 disabled:cursor-not-allowed shadow-lg hover:shadow-xl font-semibold text-sm">
                                        <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5-5a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>
                                            @if($product->total_stock_quantity <= 0)
                                                Out of Stock
                                            @else
                                                Add to Cart
                                            @endif
                                        </span>
                                    </button>
                                </div>

                                <script>
                                    function checkLoginAndAddToCart(productID) {
                                    @auth
                                        // Apply animation when adding to cart (optional)
                                        const button = event.target;
                                        button.classList.add("animate-ping");  // Animation effect

                                        // Delay to ensure animation finishes before adding to cart
                                        setTimeout(function() {
                                            addToCart(productID);
                                        }, 500);  // Adjust this value based on animation duration
                                    @else
                                        // Add a subtle animation before redirecting
                                        const button = event.target;
                                        button.classList.add("animate-bounce");  // Animation effect

                                        // Redirect after the animation finishes
                                        setTimeout(function() {
                                            window.location.href = "/login";
                                        }, 500);  // Adjust this value based on animation duration
                                    @endauth
                                    }
                                </script>

                        </article>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m-2 0v9a2 2 0 002 2h2M6 9h12m-6-4v4"></path>
                            </svg>
                            <p class="text-gray-500 text-lg mt-4">No products found matching your filters.</p>
                            <p class="text-gray-400 text-sm mt-2">Try adjusting your search criteria or clearing filters.</p>
                        </div>
                    @endforelse
                </div>
                
            <div class="flex justify-between items-center mt-6 w-full">
                
                <div class="text-gray-600 text-sm">
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                </div>

                <div class="flex justify-end">
                    {{ $products->links() }}
                </div>
            </div>

            <style>
                nav[role="navigation"] > div:first-child,
                nav[role="navigation"] > div > div:first-child {
                    display: none !important;
                }
                nav[role="navigation"] {
                    display: flex;
                    justify-content: flex-end;
                    width: 100%;
                }
            </style>




            </div>
        </div>
    </section>
</div>

<!-- Add to Cart JavaScript (optional - for quick add to cart without going to detail page) -->
<script>
function addToCart(productId) {
    // You can implement AJAX cart functionality here
    // Or redirect to product detail page
    window.location.href = `/product/${productId}`;
    
    // Alternative: AJAX call
    // fetch('/cart/add', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //     },
    //     body: JSON.stringify({product_id: productId, quantity: 1})
    // }).then(response => {
    //     // Handle response
    //     // Show success message, update cart count, etc.
    // }).catch(error => {
    //     console.error('Error adding to cart:', error);
    // });
}
</script>

