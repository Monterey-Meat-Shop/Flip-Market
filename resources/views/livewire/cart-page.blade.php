<section class="bg-white py-8 md:py-16">
  <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
    <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl">Shopping Cart</h2>

    <div class="mt-6 grid lg:flex lg:items-start gap-6">
      <div class="w-full lg:flex-1">
        @if($cartItems->isEmpty())
          <div class="p-6 bg-white rounded-lg border border-gray-200">
            <p class="text-gray-600">Your cart is empty.</p>
            <a href="{{ route('products') }}">Browse Products</a>
          </div>
        @else
          <div class="space-y-6">
            @foreach($cartItems as $item)
              <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:p-6 flex items-center gap-4">
                
                {{-- Product Image --}}
                <a href="{{ route('product.detail', $item->product->productID ?? 0) }}" class="shrink-0">
                  <img 
                    class="h-20 w-20 object-cover"
                    src="{{ $item->product && $item->product->image_path 
                        ? asset('storage/' . $item->product->image_path) 
                        : 'https://via.placeholder.com/150' }}"
                    alt="{{ $item->product->name ?? 'Unknown Product' }}" 
                  />
                </a>

                {{-- Product Info --}}
                <div class="flex-1 min-w-0">
                  <a href="{{ route('product.detail', $item->product->productID ?? 0) }}" class="text-base font-medium text-gray-900 hover:underline">
                    {{ $item->product->name ?? 'Unknown Product' }}
                  </a>
                  <p class="text-sm text-gray-500 mt-1">
                    @if($item->size) Size: {{ $item->size }} @endif
                    @if($item->colorway) • Color: {{ $item->colorway }} @endif
                  </p>
                </div>

                {{-- Quantity + Subtotal --}}
                <div class="flex items-center gap-4">
                  <div class="flex items-center">
                    <button wire:click="updateQuantity({{ $item->cart_itemID }}, {{ max(1, $item->quantity - 1) }})" class="px-2 py-1 border rounded">-</button>
                    <input type="text" readonly value="{{ $item->quantity }}" class="w-12 text-center border-0 bg-transparent" />
                    <button wire:click="updateQuantity({{ $item->cart_itemID }}, {{ $item->quantity + 1 }})" class="px-2 py-1 border rounded">+</button>
                  </div>

                  <div class="text-end">
                    <p class="text-base font-bold text-gray-900">₱{{ number_format($item->sub_total, 2) }}</p>
                    <button wire:click="removeItem({{ $item->cart_itemID }})" class="text-sm text-red-600 hover:underline mt-1">Remove</button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Sidebar Order Summary --}}
      <aside class="w-full lg:w-80">
        <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
          <p class="text-xl font-semibold text-gray-900">Order summary</p>

          <div class="space-y-4">
            <dl class="flex items-center justify-between">
              <dt class="text-base font-normal text-gray-500">Subtotal</dt>
              <dd class="text-base font-medium text-gray-900">₱{{ number_format($total ?? 0, 2) }}</dd>
            </dl>

            <a href="{{ route('cart') }}" 
   class="flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-900">
   Proceed to Checkout
</a>
            <a href="{{ route('products') }}" class="inline-flex items-center gap-2 text-sm underline mt-2">
              Continue Shopping
            </a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
