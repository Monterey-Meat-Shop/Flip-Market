<div class="bg-gray-100 min-h-screen pt-10 pb-20">
    <h1 class="text-center text-2xl font-bold text-gray-800 mb-8">All Products</h1>

    <section class="py-10 bg-gray-100">
        <div class="mx-auto max-w-7xl px-6 grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Filter Sidebar -->
            <aside class="bg-white rounded-xl p-4 shadow-md lg:col-span-1 h-fit sticky top-4">
                <h2 class="text-lg font-bold text-gray-700 mb-4">Filters</h2>


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

                <!-- Price Filter -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Price</h3>
                    <input type="range" min="100" max="10000" wire:model.live="maxPrice" class="w-full accent-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Up to ₱{{ number_format($maxPrice) }}</p>
                </div>

                <!-- Clear Filters Button -->
                <button 
                    wire:click="clearFilters" 
                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200"
                >
                    Clear All Filters
                </button>
            </aside>

            <!-- Product List -->
            <div class="lg:col-span-3">
                <!-- Results Count -->
                <div class="mb-6">
                    <p class="text-gray-600">Showing {{ $products->count() }} products</p>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @forelse($products as $product)
                        <article class="rounded-xl bg-white shadow-lg hover:shadow-xl duration-300 overflow-hidden">
                            <!-- Product Link (wraps image and content) -->
                            <a href="{{ route('product.detail', $product->productID) }}" class="block hover:scale-105 transform transition duration-300">
                                <div class="relative overflow-hidden">
                                    <img 
                                    src="{{ $product->image_path ? asset('storage/' . $product->image_path) : 'https://via.placeholder.com/300' }}" 
                                    alt="{{ $product->name }}" 
                                    class="w-full h-48 object-cover"
                                    />
                                    <!-- Optional: Add brand badge -->
                                    @if($product->brand)
                                        <span class="absolute top-2 left-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                                            {{ $product->brand->name }}
                                        </span>
                                    @endif
                                    
                                    <!-- Stock Status Badge -->
                                    @if($product->total_stock_quantity <= 0)
                                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                            Out of Stock
                                        </span>
                                    {{-- @elseif($product->total_stock_quantity <= 5)
                                        <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded">
                                            Low Stock
                                        </span> --}}
                                    @endif
                                </div>

                                <div class="p-4">
                                    <h2 class="text-slate-700 font-semibold text-lg mb-1">{{ $product->name }}</h2>
                                    <p class="text-sm text-slate-400 mb-2">
                                        {{ $product->category->name ?? 'No Category' }}
                                    </p>
                                    <p class="text-lg font-bold text-blue-500">₱{{ number_format($product->price, 2) }}</p>
                                </div>
                            </a>
                            
                            <!-- Add to Cart Button (separate from link) -->
                            <div class="p-4 pt-0">
                                <button 
                                    onclick="addToCart({{ $product->productID }})"
                                    @if($product->total_stock_quantity <= 0) disabled @endif
                                    class="w-full flex items-center justify-center space-x-2 rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
//new update, eto na chan
</script>