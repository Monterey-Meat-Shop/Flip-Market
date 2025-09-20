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
                            <li>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                                    <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="mr-2">
                                    {{ $category->name }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Brand Filter -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Brand</h3>
                    <ul class="space-y-2 text-gray-700">
                        @foreach($brands as $brand)
                            <li>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                                    <input type="checkbox" wire:model="selectedBrands" value="{{ $brand->id }}" class="mr-2">
                                    {{ $brand->name }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Price Filter -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Price</h3>
                    <input type="range" min="100" max="10000" wire:model="maxPrice" class="w-full accent-blue-500">
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
                        <article class="rounded-xl bg-white p-3 shadow-lg hover:shadow-xl hover:scale-105 duration-300 transform">
                            <div class="relative flex items-end overflow-hidden rounded-xl">
                                <img 
                                src="{{ $product->image_path ? asset('storage/' . $product->image_path) : 'https://via.placeholder.com/300' }}" 
                                alt="{{ $product->name }}" 
                                class="w-full h-48 object-cover rounded"
                                />
                                <!-- Optional: Add brand badge -->
                                @if($product->brand)
                                    <span class="absolute top-2 left-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                                        {{ $product->brand->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 p-2">
                                <h2 class="text-slate-700 font-semibold text-lg">{{ $product->name }}</h2>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $product->category->name ?? 'No Category' }}
                                </p>
                                <div class="mt-3 flex items-end justify-between">
                                    <p class="text-lg font-bold text-blue-500">₱{{ number_format($product->price, 2) }}</p>
                                    <button class="flex items-center space-x-1.5 rounded-lg bg-blue-500 px-4 py-1.5 text-white hover:bg-blue-600 transition duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5-5a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
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