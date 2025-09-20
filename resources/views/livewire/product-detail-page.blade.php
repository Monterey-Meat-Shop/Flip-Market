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
                    
                    {{-- <div class="flex text-yellow-400">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-sm text-gray-500">(4.5)</span> --}}
                </div>

                <!-- Price -->
                <p class="mt-4 text-3xl font-bold text-gray-900">₱{{ number_format($product->price, 2) }}</p>

                <!-- Variants: Colorways -->
                {{-- @if($this->getAvailableColors()->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="mb-3 text-sm font-semibold text-gray-900">Colorway</h3>
                        <div class="flex gap-2 flex-wrap">
                            @foreach($this->getAvailableColors() as $colorway)
                                @php
                                    $variant = $product->variants->where('colorway', $colorway)->first();
                                @endphp
                                <button 
                                    wire:click="selectVariant({{ $variant->id }})"
                                    class="px-3 py-2 border rounded-lg text-sm {{ $selectedColor == $colorway ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400' }}"
                                >
                                    {{ $colorway }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif --}}

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
                        Add to cart - ₱{{ number_format($product->price * $quantity, 2) }}
                    @endif
                </button>

                <!-- Success Message -->
                @if (session()->has('message'))
                    <div class="mt-3 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Shipping Options -->
                {{-- <div class="mt-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Delivery Options</h3>
                    <div class="space-y-2 text-sm">
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="shippingOption" value="shipping" class="text-blue-600"> 
                            <span>Standard Shipping - ₱150</span>
                            <span class="text-gray-500">(3-5 business days)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="shippingOption" value="express" class="text-blue-600"> 
                            <span>Express Shipping - ₱250</span>
                            <span class="text-gray-500">(1-2 business days)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="shippingOption" value="pickup" class="text-blue-600"> 
                            <span>Store Pickup - Free</span>
                            <span class="text-gray-500">(Available next day)</span>
                        </label>
                    </div>
                </div> --}}

                <!-- Additional Info -->
                {{-- <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-3 gap-4 text-center text-sm">
                        <div class="flex flex-col items-center">
                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-gray-600">Secure Payment</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <span class="text-gray-600">Easy Returns</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364"/>
                            </svg>
                            <span class="text-gray-600">1 Year Warranty</span>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</section>