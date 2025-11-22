<div>
<section class="py-8 antialiased min-h-screen bg-gray-50">
    <div class="mx-auto max-w-screen-xl px-4 lg:grid lg:grid-cols-12 lg:gap-8 mt-20" x-data="{ 
        animating: false,
        cartPosition: null,
        imagePosition: null,
        init() {
            // Get cart icon position on mount
            this.$nextTick(() => {
                const cartIcon = document.querySelector('[data-cart-icon]');
                if (cartIcon) {
                    const rect = cartIcon.getBoundingClientRect();
                    this.cartPosition = { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
                }
            });
        }
    }">

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
                <div class="col-span-7" x-ref="mainImage">
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

                <!-- Stock Status / Pre-order Badge -->
                <div class="mt-4 flex items-center gap-2">
                    @if($product->status === 'pre_order')
                        <span class="text-xs px-2.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-semibold">
                            PRE-ORDER
                        </span>
                    @elseif($product->total_stock_quantity <= 0)
                        <span class="text-xs px-2.5 py-0.5 rounded bg-red-100 text-red-600 font-semibold">
                            OUT OF STOCK
                        </span>
                    @elseif($product->total_stock_quantity <= 2)
                        <span class="text-xs px-2.5 py-0.5 rounded bg-orange-100 text-orange-600 font-semibold">
                            ONLY {{ $product->total_stock_quantity }} LEFT!
                        </span>
                    @else
                        <span class="text-xs px-2.5 py-0.5 rounded bg-green-100 text-green-600 font-semibold">
                            IN STOCK
                        </span>
                    @endif
                </div>

                <!-- Price -->
                @php
                    $basePrice = (isset($selectedVariant) && $product->variants->isNotEmpty())
                        ? ($selectedVariant->price ?? $product->price)
                        : $product->price;

                    $originalPrice = $basePrice;

                    $activeDiscount = $product->discounts
                        ->where('is_active', true)
                        ->filter(function ($discount) {
                            return (is_null($discount->start_date) || $discount->start_date <= now())
                                && (is_null($discount->end_date) || $discount->end_date >= now());
                        })
                        ->first();

                    // Compute final (discounted) price
                    if ($activeDiscount) {
                        if ($activeDiscount->discount_type === 'Percentage') {
                            $finalPrice = $basePrice - ($basePrice * ($activeDiscount->discount_value / 100));
                        } else {
                            $finalPrice = $basePrice - $activeDiscount->discount_value;
                        }
                        $finalPrice = max($finalPrice, 0);
                    } else {
                        $finalPrice = $basePrice;
                    }
                @endphp


                <div class="mt-4 flex items-baseline gap-3">
                    @if($activeDiscount)
                        <span class="text-3xl font-bold text-black-700">
                            ₱{{ number_format($finalPrice, 2) }}
                        </span>
                        <span class="text-lg text-gray-400 line-through">
                            ₱{{ number_format($originalPrice, 2) }}
                        </span>
                        <span class="text-sm text-red-600 font-semibold">
                            ({{ $activeDiscount->discount_value }}{{ $activeDiscount->discount_type === 'Percentage' ? '%' : '₱' }} OFF)
                        </span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">
                            ₱{{ number_format($originalPrice, 2) }}
                        </span>
                    @endif
                </div>

                <!-- Variants: Colors -->
                @if($this->getAvailableColors()->isNotEmpty())
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Colorway</h3>

                        <div class="flex flex-wrap gap-3">
                            @foreach($this->getAvailableColors() as $color)
                                <button 
                                    wire:click="selectColorway('{{ $color }}')"
                                    type="button"
                                    class="px-3 py-2 border rounded-lg text-sm transition
                                        {{ $selectedColor == $color ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400' }}">
                    
                                    <!-- Optional small swatch -->
                                    <span class="inline-block w-3 h-3 mr-2 rounded-full align-middle"
                                          style="background: {{ $color }}; border: 1px solid rgba(0,0,0,0.05);"></span>

                                    <span class="align-middle">{{ $color }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Variants: Sizes - UPDATED SECTION -->
                @if($this->getAvailableSizes()->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="mb-3 text-sm font-semibold text-gray-900">Size</h3>
                        <div class="flex flex-wrap gap-3">
                            @foreach($this->getAvailableSizes() as $size)
                                <button 
                                    wire:click="selectSize('{{ $size }}')"
                                    class="px-3 py-2 border rounded-lg text-sm transition
                                    {{ $selectedSize == $size ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400' }}">
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity + Stock + Add to Favorites -->
                <div class="mt-6 flex items-center flex-wrap gap-3">

                   <!-- Quantity -->
                   <div class="flex items-center gap-2">
                       <label for="quantity" class="text-sm font-medium text-gray-900">Quantity:</label>
                       <input
                           wire:model.live="quantity"
                           id="quantity"
                           type="number"
                           min="1"
                           max="{{ $this->getCurrentStock() }}"
                           step="1"
                           class="w-20 border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                           oninput="
                               if (this.value === '' || parseInt(this.value) < 1) {
                                   this.value = 1;
                               }
                               const max = {{ (int) $this->getCurrentStock() }};
                               if (parseInt(this.value) > max) {
                                   this.value = max;
                               }
                           "
                       >
                   </div>

                    <!-- Stock info (styled same as buttons) -->
                    @if($selectedVariant)
                        <div class="flex items-center">
                            @if($selectedVariant->status === 'pre_order')
                                {{-- <div class="px-4 py-2 border border-yellow-300 bg-yellow-50 text-yellow-700 rounded-lg text-sm font-medium">
                                    PRE-ORDER
                                </div> --}}
                            @elseif($selectedVariant->stock_quantity <= 0)
                                <div class="px-4 py-2 border border-red-300 bg-red-50 text-red-600 rounded-lg text-sm font-medium">
                                    OUT OF STOCK
                                </div>
                            @elseif($selectedVariant->stock_quantity <= 2)
                                <div class="px-4 py-2 border border-orange-300 bg-orange-50 text-orange-600 rounded-lg text-sm font-medium">
                                    ONLY {{ $selectedVariant->stock_quantity }} LEFT!
                                </div>
                            @else
                                <div class="px-4 py-2 border border-green-300 bg-green-50 text-green-700 rounded-lg text-sm font-medium">
                                    {{ $selectedVariant->stock_quantity }} available
                                </div>
                            @endif
                        </div>
                    @endif

                    @php
                        $isFavorited = false;
                        if (Auth::check()) {
                            $customer = \App\Models\Customer::where('user_id', Auth::id())->first();
                            if ($customer) {
                                $isFavorited = \App\Models\Favorite::where('customerID', $customer->customerID)
                                    ->where('productID', $product->productID)
                                    ->exists();
                            }
                        }
                    @endphp

                    <!-- Add to favorites -->
                    <button wire:click="addToFavorites" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 {{ $isFavorited ? 'text-red-500 fill-current' : '' }}" 
                             fill="{{ $isFavorited ? 'currentColor' : 'none' }}" 
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        {{ $isFavorited ? 'Remove favorites' : 'Add to favorites' }}
                    </button>
                </div>

                <!-- Add to Cart Button -->
                <button 
                    @click="
                        if (!animating && {{ $this->getCurrentStock() }} > 0) {
                            animating = true;
                            const img = $refs.mainImage.querySelector('img');
                            const imgRect = img.getBoundingClientRect();
                            imagePosition = { x: imgRect.left + imgRect.width / 2, y: imgRect.top + imgRect.height / 2 };
                            
                            $wire.addToCart().then(() => {
                                setTimeout(() => { animating = false; }, 1000);
                            });
                        }
                    "
                    :disabled="animating || {{ $this->getCurrentStock() }} <= 0"
                    class="mt-4 w-full px-5 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition duration-200 relative overflow-hidden"
                >
                    <span :class="animating ? 'opacity-50' : ''">
                        @if($this->getCurrentStock() <= 0)
                            Out of Stock
                        @else
                            Add to cart - ₱{{ number_format($this->getCurrentPrice() * $quantity, 2) }}
                        @endif
                    </span>
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

        <!-- Flying Image Animation -->
        <div 
            x-show="animating" 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed pointer-events-none z-50"
            :style="`
                left: ${imagePosition?.x}px; 
                top: ${imagePosition?.y}px;
                transform: translate(-50%, -50%);
            `"
        >
            <div 
                class="relative"
                x-init="
                    if (animating && cartPosition) {
                        const deltaX = cartPosition.x - imagePosition.x;
                        const deltaY = cartPosition.y - imagePosition.y;
                        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
                        const controlX = deltaX * 0.5;
                        const controlY = -distance * 0.4;
                        
                        $el.animate([
                            { 
                                transform: 'translate(0, 0) scale(1) rotate(0deg)',
                                opacity: 1,
                                filter: 'blur(0px) brightness(1)'
                            },
                            { 
                                transform: `translate(${controlX * 0.5}px, ${controlY * 0.3}px) scale(0.95) rotate(${deltaX > 0 ? 10 : -10}deg)`,
                                opacity: 1,
                                filter: 'blur(0px) brightness(1.1)',
                                offset: 0.2
                            },
                            { 
                                transform: `translate(${controlX}px, ${controlY}px) scale(0.85) rotate(${deltaX > 0 ? 25 : -25}deg)`,
                                opacity: 1,
                                filter: 'blur(0.5px) brightness(1.2)',
                                offset: 0.4
                            },
                            { 
                                transform: `translate(${controlX + (deltaX - controlX) * 0.5}px, ${controlY + (deltaY - controlY) * 0.3}px) scale(0.6) rotate(${deltaX > 0 ? 45 : -45}deg)`,
                                opacity: 0.95,
                                filter: 'blur(1px) brightness(1.1)',
                                offset: 0.65
                            },
                            { 
                                transform: `translate(${deltaX * 0.9}px, ${deltaY * 0.85}px) scale(0.35) rotate(${deltaX > 0 ? 70 : -70}deg)`,
                                opacity: 0.8,
                                filter: 'blur(1.5px) brightness(1)',
                                offset: 0.85
                            },
                            { 
                                transform: `translate(${deltaX}px, ${deltaY}px) scale(0.05) rotate(${deltaX > 0 ? 90 : -90}deg)`,
                                opacity: 0,
                                filter: 'blur(2px) brightness(0.8)'
                            }
                        ], {
                            duration: 1100,
                            easing: 'cubic-bezier(0.34, 1.56, 0.64, 1)'
                        });
                    }
                "
            >
                <div class="relative animate-pulse-ring">
                    @if($product->image_path)
                        @php
                            $images = is_array($product->image_url) ? $product->image_url : (json_decode($product->image_url, true) ?: [$product->image_path]);
                            $currentImage = $images[$selectedImage] ?? $images[0] ?? $product->image_path;
                        @endphp
                        <img 
                            src="{{ asset('storage/' . $currentImage) }}" 
                            class="w-24 h-24 object-cover rounded-xl shadow-2xl border-3 border-blue-400 ring-4 ring-blue-200"
                            alt="{{ $product->name }}"
                        />
                    @else
                        <img 
                            src="https://via.placeholder.com/96" 
                            class="w-24 h-24 object-cover rounded-xl shadow-2xl border-3 border-blue-400 ring-4 ring-blue-200"
                            alt="{{ $product->name }}"
                        />
                    @endif
                    
                    <!-- Quantity Badge with Pulse -->
                    <div class="absolute -top-2 -right-2 bg-gradient-to-br from-blue-500 to-blue-700 text-white text-sm font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-lg animate-badge-pop border-2 border-white">
                        {{ $quantity ?? 1 }}
                    </div>
                    
                    <!-- Sparkle Effects -->
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute top-0 left-0 w-2 h-2 bg-yellow-400 rounded-full animate-sparkle" style="animation-delay: 0s;"></div>
                        <div class="absolute top-1/4 right-0 w-1.5 h-1.5 bg-blue-300 rounded-full animate-sparkle" style="animation-delay: 0.2s;"></div>
                        <div class="absolute bottom-1/4 left-1/4 w-2 h-2 bg-purple-400 rounded-full animate-sparkle" style="animation-delay: 0.4s;"></div>
                        <div class="absolute bottom-0 right-1/4 w-1.5 h-1.5 bg-pink-400 rounded-full animate-sparkle" style="animation-delay: 0.3s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes cart-bounce {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.3) translateY(-5px); }
        50% { transform: scale(1.15) translateY(-2px); }
        75% { transform: scale(1.25) translateY(-3px); }
    }
    
    @keyframes badge-pop {
        0% { transform: scale(0) rotate(-180deg); }
        60% { transform: scale(1.2) rotate(10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    
    @keyframes pulse-ring {
        0% { 
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
        }
        50% { 
            box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
        }
        100% { 
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
        }
    }
    
    @keyframes sparkle {
        0%, 100% { 
            opacity: 0;
            transform: scale(0) translateY(0);
        }
        50% { 
            opacity: 1;
            transform: scale(1.5) translateY(-10px);
        }
    }
    
    [data-cart-icon].cart-added {
        animation: cart-bounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    .animate-badge-pop {
        animation: badge-pop 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    .animate-pulse-ring {
        animation: pulse-ring 1.2s ease-out infinite;
    }
    
    .animate-sparkle {
        animation: sparkle 0.8s ease-out;
    }
</style>

<script>
    // Add this attribute to your cart icon in the navigation
    // Example: <svg data-cart-icon class="w-6 h-6" ... >
    
    // Listen for cart updates to animate the cart icon
    document.addEventListener('livewire:load', function () {
        Livewire.on('cartUpdated', () => {
            const cartIcon = document.querySelector('[data-cart-icon]');
            if (cartIcon) {
                cartIcon.classList.add('cart-added');
                setTimeout(() => {
                    cartIcon.classList.remove('cart-added');
                }, 500);
            }
        });
    });
</script>
</div>