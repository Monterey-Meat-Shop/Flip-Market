<div>
  <section class="bg-white py-8 md:py-7 h-screen">
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
              @foreach ($cartItems as $item)
                <div class="flex items-center justify-between bg-white shadow-sm rounded-xl p-4 mb-4">
                  <div class="flex items-center space-x-4">
                    {{-- Product Image --}}
                    <img src="{{ asset('storage/' . $item->product->image_path) }}"
                         alt="{{ $item->product->name }}"
                         class="w-20 h-20 object-cover rounded">

                    <div>
                      {{-- Product Name --}}
                      <h3 class="text-lg font-semibold text-gray-800">
                        {{ $item->product->name }}

                        {{-- Discount Badge (if applicable) --}}
                        @php
                            $activeDiscount = $item->product->discounts()
                                ->where('is_active', true)
                                ->where(function ($q) {
                                    $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                                })
                                ->where(function ($q) {
                                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                                })
                                ->first();
                        @endphp

                        @if ($activeDiscount)
                          <span class="inline-block ml-2 text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">
                            {{ $activeDiscount->discount_value }}
                            {{ $activeDiscount->discount_type === 'Percentage' ? '%' : '₱' }} OFF
                          </span>
                        @endif
                      </h3>

                      {{-- Product details --}}
                      <p class="text-sm text-gray-500">
                        Size: {{ $item->size }} • Color: {{ $item->color }}
                      </p>

                      {{-- Price Display --}}
                      @if ($activeDiscount)
                        <p class="text-sm line-through text-gray-400">
                          ₱{{ number_format($item->product->price, 2) }}
                        </p>
                        <p class="text-base font-semibold text-red-600">
                          ₱{{ number_format($item->product->discounted_price, 2) }}
                        </p>
                      @else
                        <p class="text-base font-semibold text-gray-800">
                          ₱{{ number_format($item->product->price, 2) }}
                        </p>
                      @endif
                    </div>
                  </div>

                  {{-- Quantity Controls and Remove --}}
                  <div class="flex items-center space-x-4">
                      <div class="flex flex-col items-center">
                          <div class="flex items-center border rounded">
                              <button wire:click="decreaseQuantity({{ $item->cart_itemID }})" class="px-3 py-1 hover:bg-gray-100 transition">-</button>
                              <span class="px-3">{{ $item->quantity }}</span>
                              <button wire:click="increaseQuantity({{ $item->cart_itemID }})" class="px-3 py-1 hover:bg-gray-100 transition">+</button>
                          </div>
                          {{-- Available Stock Display --}}
                          @if($item->variant)
                              @php
                                  // Show available stock PLUS what's already in cart
                                  $totalAvailable = $item->variant->stock_quantity + $item->quantity;
                              @endphp
                              <span class="text-xs text-gray-500 mt-1">
                                  {{ $item->variant->stock_quantity }} more available
                              </span>
                          @endif
                      </div>

                      {{-- Subtotal --}}
                      <p class="font-semibold text-gray-800">
                          ₱{{ number_format($item->sub_total, 2) }}
                      </p>

                      {{-- Remove Button --}}
                      <button wire:click="removeFromCart({{ $item->cart_itemID }})" class="text-red-600 hover:text-red-800 transition">
                          Remove
                      </button>
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

                {{-- Total Savings --}}
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
</div>