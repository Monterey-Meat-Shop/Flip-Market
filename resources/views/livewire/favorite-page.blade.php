<div class="max-w-6xl mx-auto py-10 px-4">
    <h2 class="text-2xl font-semibold mb-6">My Favorites</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if ($favorites->isEmpty())
        <p class="text-gray-500">You don’t have any favorites yet.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($favorites as $favorite)
                @php
                    $product = $favorite->product;

                    // Safely handle image data (string, array, or JSON)
                    $imageFile = null;
                    if (is_array($product->image_url)) {
                        $imageFile = $product->image_url[0] ?? null;
                    } else {
                        $decoded = json_decode($product->image_url, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $imageFile = $decoded[0] ?? null;
                        } else {
                            $imageFile = $product->image_url;
                        }
                    }
                    $imagePath = $imageFile
                        ? (Str::startsWith($imageFile, ['http', 'https'])
                            ? $imageFile
                            : asset('storage/products/' . ltrim($imageFile, '/')))
                        : asset('images/placeholder.png');
                @endphp

                <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition duration-300">
                    <a href="{{ route('product.detail', ['productId' => $product->productID]) }}">
                        <div class="relative">
                            <img 
                                src="{{ $product->image_path ? asset('storage/' . $product->image_path) : asset('images/placeholder.png') }}" 
                                alt="{{ $product->product_name }}" 
                            />

                            {{-- Brand badge --}}
                            @if (!empty($product->brand?->name))
                                <span class="absolute top-3 left-3 bg-black text-white text-[10px] px-2 py-1 rounded">
                                    {{ $product->brand->name }}
                                </span>
                            @endif

                            {{-- Status or sale badge --}}
                            @if ($product->status === 'pre-order')
                                <span class="absolute top-3 right-3 bg-yellow-500 text-white text-[10px] px-2 py-1 rounded">
                                    PRE-ORDER
                                </span>
                            @elseif(!empty($product->discount_price) && $product->discount_price < $product->price)
                                <span class="absolute top-3 right-3 bg-red-600 text-white text-[10px] px-2 py-1 rounded">
                                    SALE
                                </span>
                            @endif
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 text-base truncate">
                                {{ $product->product_name }}
                            </h3>
                            <p class="text-gray-500 text-sm">{{ $product->category->name ?? 'Sneaker' }}</p>

                            <div class="mt-2">
                                @if (!empty($product->discount_price))
                                    <p class="text-gray-400 text-sm line-through">₱{{ number_format($product->price, 2) }}</p>
                                    <p class="text-red-600 font-bold">₱{{ number_format($product->discount_price, 2) }}</p>
                                @else
                                    <p class="text-blue-700 font-bold">₱{{ number_format($product->price, 2) }}</p>
                                @endif
                            </div>
                        </div>
                    </a>

                    <div class="p-3 border-t border-gray-100">
                        <button 
                            wire:click="removeFavorite({{ $favorite->favoriteID }})"
                            class="w-full text-sm bg-red-600 hover:bg-red-800 text-white rounded-lg py-2">
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
