<section class="bg-white py-8 md:py-16">
  <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
    <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl">Shopping Cart</h2>

    <div class="mt-6 grid lg:flex lg:items-start gap-6">
      <div class="w-full lg:flex-1">
        @if($cartItems->isEmpty())
          <div class="p-6 bg-white rounded-lg border border-gray-200">
            <p class="text-gray-600">Your cart is empty.</p>
            <a href="{{ route('products') }}" class="text-blue-600 hover:underline mt-2 inline-block">Browse Products</a>
          </div>
        @else
          <div class="space-y-6">
            @foreach($cartItems as $item)
              <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:p-6 flex items-center gap-4">
                
                {{-- Product Image --}}
                <a href="{{ route('product.detail', $item->product->productID ?? 0) }}" class="shrink-0">
                  <img 
                    class="h-20 w-20 object-cover rounded"
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
                    @if($item->colorway) {{ $item->size ? '•' : '' }} Color: {{ $item->colorway }} @endif
                  </p>
                  
                  {{-- Show discount badge if applicable --}}
                  @if(isset($item->active_discount))
                    <span class="inline-block mt-1 text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">
                      {{ $item->active_discount->discount_value }}{{ $item->active_discount->discount_type === 'Percentage' ? '%' : '₱' }} OFF
                    </span>
                  @endif
                  
                  {{-- Show original price if discounted --}}
                  @if(isset($item->active_discount) && isset($item->original_price))
                    <div class="mt-1">
                      <span class="text-sm text-gray-400 line-through">₱{{ number_format($item->original_price, 2) }}</span>
                      <span class="text-sm font-semibold text-blue-600 ml-2">₱{{ number_format($item->unit_price, 2) }}</span>
                    </div>
                  @else
                    <div class="mt-1">
                      <span class="text-sm text-gray-600">₱{{ number_format($item->unit_price, 2) }}</span>
                    </div>
                  @endif
                </div>

                {{-- Quantity + Subtotal --}}
                <div class="flex items-center gap-4">
                  <div class="flex items-center border rounded-lg">
                    <button 
                      wire:click="updateQuantity({{ $item->cart_itemID }}, {{ max(1, $item->quantity - 1) }})" 
                      class="px-3 py-2 hover:bg-gray-100 transition"
                    >
                      -
                    </button>
                    <input 
                      type="text" 
                      readonly 
                      value="{{ $item->quantity }}" 
                      class="w-12 text-center border-0 bg-transparent font-medium" 
                    />
                    <button 
                      wire:click="updateQuantity({{ $item->cart_itemID }}, {{ $item->quantity + 1 }})" 
                      class="px-3 py-2 hover:bg-gray-100 transition"
                    >
                      +
                    </button>
                  </div>

                  <div class="text-end min-w-[100px]">
                    <p class="text-base font-bold text-gray-900">₱{{ number_format($item->sub_total, 2) }}</p>
                    <button 
                      wire:click="removeItem({{ $item->cart_itemID }})" 
                      class="text-sm text-red-600 hover:underline mt-1"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif

        {{-- Flash Messages --}}
        @if (session()->has('message'))
          <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
          </div>
        @endif

        @if (session()->has('error'))
          <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
          </div>
        @endif
      </div>

      {{-- Sidebar Order Summary --}}
      <aside class="w-full lg:w-80">
        <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sticky top-4">
          <p class="text-xl font-semibold text-gray-900">Order summary</p>

          <div class="space-y-4">
            <div class="space-y-2">
              <dl class="flex items-center justify-between">
                <dt class="text-base font-normal text-gray-500">Subtotal</dt>
                <dd class="text-base font-medium text-gray-900">₱{{ number_format($total ?? 0, 2) }}</dd>
              </dl>

              {{-- Show total savings if any discounts applied --}}
              @php
                $totalSavings = 0;
                foreach($cartItems as $item) {
                  if(isset($item->active_discount) && isset($item->original_price)) {
                    $totalSavings += ($item->original_price - $item->unit_price) * $item->quantity;
                  }
                }
              @endphp

              @if($totalSavings > 0)
                <dl class="flex items-center justify-between text-green-600">
                  <dt class="text-base font-normal">Total Savings</dt>
                  <dd class="text-base font-semibold">-₱{{ number_format($totalSavings, 2) }}</dd>
                </dl>
              @endif
            </div>

            <dl class="flex items-center justify-between border-t pt-4">
              <dt class="text-lg font-bold text-gray-900">Total</dt>
              <dd class="text-lg font-bold text-blue-700">₱{{ number_format($total ?? 0, 2) }}</dd>
            </dl>

            @if(!$cartItems->isEmpty())
              <a 
                href="{{ route('checkout') }}" 
                class="flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 transition"
              >
                Proceed to Checkout
              </a>
            @endif
            
            <a 
              href="{{ route('products') }}" 
              class="inline-flex items-center gap-2 text-sm text-blue-700 hover:underline"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Continue Shopping
            </a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>