<section class="py-8 antialiased min-h-screen bg-gray-50">
    <div class="mx-auto max-w-screen-xl px-4 lg:grid lg:grid-cols-12 lg:gap-8 mt-20">

        <!-- Left: Images -->
        <div class="lg:col-span-7">
            <div class="grid grid-cols-8 gap-4">
                
                <!-- Thumbnails -->
                <div class="flex flex-col gap-4 col-span-1">
                    @if($product->image_path)
                        @php
                            $images = is_array($product->image_url) ? $product->image_url : (json_decode($product->image_url, true) ?: [$product->image_path]);
                        @endphp
                        @foreach($images as $index => $image)
                            <button 
                                wire:click="selectImage({{ $index }})"
                                class="border rounded-lg p-2 {{ $selectedImage == $index ? 'ring-2 ring-blue-500 border-blue-500' : 'hover:border-gray-400' }}"
                            >
                                <img src="{{ asset('storage/' . $image) }}" class="h-12 w-12 object-cover rounded" alt="Product thumbnail">
                            </button>
                        @endforeach
                    @else
                        <button class="border rounded-lg p-2 ring-2 ring-blue-500 border-blue-500">
                            <img src="https://via.placeholder.com/48" class="h-12 w-12 object-cover rounded" alt="Product thumbnail">
                        </button>
                    @endif
                </div>

                <!-- Main Image -->
                <div class="col-span-7">
                    @if($product->image_path)
                        @php
                            $images = is_array($product->image_url) ? $product->image_url : (json_decode($product->image_url, true) ?: [$product->image_path]);
                            $currentImage = $images[$selectedImage] ?? $images[0] ?? $product->image_path;
                        @endphp
                        <img class="w-full h-96 rounded-lg object-cover" src="{{ asset('storage/' . $currentImage) }}" alt="{{ $product->name }}" />
                    @else
                        <img class="w-full h-96 rounded-lg object-cover" src="https://via.placeholder.com/600x400" alt="{{ $product->name }}" />
                    @endif
                </div>
            </div>

            <!-- Product details -->
            <div class="mt-8 bg-white rounded-lg p-6 shadow-sm">
                <h2 class="font-semibold text-lg mb-3">Product Details</h2>
                <div class="text-gray-600 text-sm leading-relaxed">
                    @if($product->description)
                      {!! str_replace(['<p>', '</p>', '&nbsp;'], ['', '', ' '], nl2br(htmlspecialchars_decode($product->description))) !!}
                    @else
                        This {{ $product->name }} is a high-quality product from {{ $product->brand->name ?? 'our premium collection' }}. 
                        It features excellent craftsmanship and attention to detail.
                        <br><br>
                        Perfect for those who appreciate quality and style. This product is designed to meet the needs of discerning customers.
                    @endif
                </div>
                
                <!-- Product Specifications -->
                <div class="mt-6">
                    <h3 class="font-medium text-gray-900 mb-2">Specifications</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Brand:</span>
                            <span class="ml-2 font-medium">{{ $product->brand->name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Category:</span>
                            <span class="ml-2 font-medium">{{ $product->category->name ?? 'N/A' }}</span>
                        </div>
                        @if($selectedVariant && $selectedVariant->colorway)
                        <div>
                            <span class="text-gray-500">Colorway:</span>
                            <span class="ml-2 font-medium">{{ $selectedVariant->colorway }}</span>
                        </div>
                        @endif
                        @if($selectedVariant && $selectedVariant->size)
                        <div>
                            <span class="text-gray-500">Size:</span>
                            <span class="ml-2 font-medium">{{ $selectedVariant->size }}</span>
                        </div>
                        @endif
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <span class="ml-2 font-medium capitalize">{{ str_replace('_', ' ', $product->status) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">SKU:</span>
                            <span class="ml-2 font-medium">{{ $product->slug }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Product Info -->
        <div class="mt-6 lg:mt-0 lg:col-span-5">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $product->name }}
                </h1>

                <!-- Brand and Category -->
                <div class="flex items-center gap-2 mt-2">
                    @if($product->brand)
                        <span class="text-sm text-gray-500">by</span>
                        <span class="text-sm font-medium text-blue-600">{{ $product->brand->name }}</span>
                    @endif
                    @if($product->category)
                        <span class="text-sm text-gray-400">•</span>
                        <span class="text-sm text-gray-500">{{ $product->category->name }}</span>
                    @endif
                    @if($selectedVariant && $selectedVariant->colorway)
                        <span class="text-sm text-gray-400">•</span>
                        <span class="text-sm text-gray-500">{{ $selectedVariant->colorway }}</span>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="flex items-center gap-2 mt-3">
                    @if($this->getCurrentStock() <= 2 && $this->getCurrentStock() > 0)
                        <span class="text-xs px-2.5 py-0.5 rounded bg-red-100 text-red-600">Only {{ $this->getCurrentStock() }} left!</span>
                    @elseif($this->getCurrentStock() > 0)
                        <span class="text-xs px-2.5 py-0.5 rounded bg-green-100 text-green-600">In Stock</span>
                    @else
                        <span class="text-xs px-2.5 py-0.5 rounded bg-gray-100 text-gray-600">Out of Stock</span>
                    @endif
                </div>

                <!-- Price -->
                <p class="mt-4 text-3xl font-bold text-gray-900">₱{{ number_format($this->getCurrentPrice(), 2) }}</p>

                <!-- Variants: Sizes -->
                @if($this->getAvailableSizes()->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="mb-3 text-sm font-semibold text-gray-900">Size</h3>
                        <div class="flex gap-2 flex-wrap">
                            @foreach($this->getAvailableSizes() as $size)
                                @php
                                    $variant = $product->variants->where('size', $size)->first();
                                @endphp
                                <button 
                                    wire:click="selectVariant({{ $variant->id }})"
                                    class="px-3 py-2 border rounded-lg text-sm {{ $selectedSize == $size ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400' }}"
                                >
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity + Add to Favorites -->
                <div class="mt-6 flex items-center gap-3">
                    <label for="quantity" class="text-sm font-medium text-gray-900">Quantity:</label>
                    <select wire:model="quantity" id="quantity" class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        @for($i = 1; $i <= min(10, $this->getCurrentStock()); $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <button 
                        wire:click="addToFavorites"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        Add to favorites
                    </button>
                </div>

                <!-- Add to Cart Button -->
                <button 
                    wire:click="addToCart"
                    @if($this->getCurrentStock() <= 0) disabled @endif
                    class="mt-4 w-full px-5 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition duration-200"
                >
                    @if($this->getCurrentStock() <= 0)
                        Out of Stock
                    @else
                        Add to cart - ₱{{ number_format($this->getCurrentPrice() * $quantity, 2) }}
                    @endif
                </button>

                <!-- Messages -->
                @if (session()->has('message'))
                    <div class="mt-3 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mt-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>