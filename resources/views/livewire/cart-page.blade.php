<section class="bg-white py-8 md:py-16">
  <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
    <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl">Shopping Cart</h2>

    {{-- Stock Issues Warning --}}
    @if(!empty($stockIssues))
      <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">⚠️ Stock Issues Detected</h3>
            <div class="mt-2 text-sm text-red-700">
              <p class="mb-2">The following items exceed available stock:</p>
              <ul class="list-disc list-inside space-y-1">
                @foreach($stockIssues as $issue)
                  <li>
                    <strong>{{ $issue['product'] }}</strong>
                    @if($issue['size']) (Size: {{ $issue['size'] }}) @endif
                    @if($issue['colorway']) (Color: {{ $issue['colorway'] }}) @endif
                    - Requested: <strong>{{ $issue['requested'] }}</strong>, Available: <strong>{{ $issue['available'] }}</strong>
                  </li>
                @endforeach
              </ul>
              <p class="mt-2 font-medium">Please update quantities to proceed with checkout.</p>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="mt-6 grid lg:flex lg:items-start gap-6">
      <div class="w-full lg:flex-1">
        @if($cartItems->isEmpty())
          <div class="p-6 bg-white rounded-lg border border-gray-200">
            <p class="text-gray-600">Your cart is empty.</p>
            <a href="{{ route('products') }}" class="text-blue-600 hover:underline">Browse Products</a>
          </div>
        @else
          <div class="space-y-6">
            @foreach($cartItems as $item)
              @php
                // Get current warehouse stock (items already in cart have been deducted)
                $warehouseStock = $item->variant 
                    ? $item->variant->stock_quantity 
                    : $item->product->total_stock_quantity;
                
                // ACCURATE CALCULATION:
                // Real total stock = warehouse + what user has in cart
                // This is the MAXIMUM the user can have
                $realTotalStock = $warehouseStock + $item->quantity;
                
                // Check if at limit (can't add more)
                $isAtLimit = $item->quantity >= $realTotalStock;
                
                // Check if over limit (validation error)
                $hasStockIssue = $item->quantity > $realTotalStock;
              @endphp

              <div class="rounded-lg border {{ $hasStockIssue ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' }} p-4 shadow-sm md:p-6">
                <div class="flex items-center gap-4">
                  
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
                      @if($item->colorway) • Color: {{ $item->colorway }} @endif
                    </p>
                    
                    {{-- Real-time Stock Info --}}
                    <div class="mt-2 text-xs">
                      <span class="inline-flex items-center px-2 py-0.5 rounded {{ $warehouseStock > 0 ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                        Warehouse: {{ $warehouseStock }} left
                      </span>
                      <span class="ml-2 text-gray-600">
                        • Total available: {{ $realTotalStock }}
                      </span>
                    </div>
                    
                    {{-- Stock Issue Warning for this item --}}
                    @if($hasStockIssue)
                      <p class="text-xs text-red-600 font-medium mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Only {{ $realTotalStock }} available - please reduce quantity to {{ $realTotalStock }} or less
                      </p>
                    @endif
                  </div>

                  {{-- Quantity Controls + Subtotal --}}
                  <div class="flex items-center gap-4">
                    <div class="flex flex-col items-center gap-2">
                      <div class="flex items-center border border-gray-300 rounded">
                        {{-- Decrease Button --}}
                        <button 
                          wire:click="updateQuantity({{ $item->cart_itemID }}, {{ max(1, $item->quantity - 1) }})" 
                          class="px-3 py-2 hover:bg-gray-100 transition"
                          type="button"
                          title="Decrease quantity"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                          </svg>
                        </button>

                        {{-- Quantity Display --}}
                        <input 
                          type="text" 
                          readonly 
                          value="{{ $item->quantity }}" 
                          class="w-16 text-center border-0 bg-white font-semibold {{ $hasStockIssue ? 'text-red-600' : 'text-gray-900' }}" 
                        />

                        {{-- Increase Button --}}
                        <button 
                          wire:click="updateQuantity({{ $item->cart_itemID }}, {{ $item->quantity + 1 }})"
                          class="px-3 py-2 transition {{ $isAtLimit ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100' }}"
                          type="button"
                          @if($isAtLimit) 
                            disabled 
                            title="Maximum stock ({{ $realTotalStock }}) reached"
                          @endif
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                          </svg>
                        </button>
                      </div>

                      {{-- Stock Status Badge --}}
                      @if($isAtLimit)
                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-0.5 rounded">
                          Max Reached
                        </span>
                      @elseif($warehouseStock <= 3 && $warehouseStock > 0)
                        <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded">
                          Low Stock
                        </span>
                      @endif
                    </div>

                    <div class="text-end min-w-[100px]">
                      <p class="text-base font-bold text-gray-900">₱{{ number_format($item->sub_total, 2) }}</p>
                      <p class="text-xs text-gray-500 mt-0.5">₱{{ number_format($item->unit_price, 2) }} each</p>
                      <button 
                        wire:click="removeItem({{ $item->cart_itemID }})" 
                        class="text-sm text-red-600 hover:underline mt-2 inline-flex items-center gap-1"
                        type="button"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Sidebar Order Summary --}}
      <aside class="w-full lg:w-80">
        <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sticky top-4">
          <p class="text-xl font-semibold text-gray-900">Order Summary</p>

          <div class="space-y-4">
            <dl class="flex items-center justify-between text-sm">
              <dt class="text-gray-600">Total Items</dt>
              <dd class="font-medium text-gray-900">{{ $cartCount }}</dd>
            </dl>

            <dl class="flex items-center justify-between pt-4 border-t border-gray-200">
              <dt class="text-lg font-semibold text-gray-900">Subtotal</dt>
              <dd class="text-lg font-bold text-gray-900">₱{{ number_format($total ?? 0, 2) }}</dd>
            </dl>

            {{-- Checkout Button with Validation --}}
            @if($canCheckout && !$cartItems->isEmpty())
              <button 
                wire:click="proceedToCheckout"
                class="flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-medium text-white hover:bg-blue-700 transition shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                Proceed to Checkout
              </button>
            @else
              <div>
                <button 
                  disabled
                  class="flex w-full items-center justify-center rounded-lg bg-gray-300 px-5 py-3 text-sm font-medium text-gray-500 cursor-not-allowed"
                  title="{{ $cartItems->isEmpty() ? 'Cart is empty' : 'Please fix stock issues first' }}">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  Checkout Unavailable
                </button>
                @if(!$canCheckout && !$cartItems->isEmpty())
                  <p class="text-xs text-red-600 text-center mt-2 font-medium">
                    ⚠️ Fix stock issues above to continue
                  </p>
                @endif
              </div>
            @endif

            <a href="{{ route('products') }}" class="flex items-center justify-center gap-2 text-sm text-blue-600 hover:text-blue-700 mt-4 hover:underline">
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

  {{-- Toast Notifications --}}
  <div 
    x-data="{ show: false, message: '', type: 'info' }" 
    x-show="show" 
    x-transition.opacity.duration.300ms
    @click="show = false"
    style="display: none;"
    class="fixed bottom-4 right-4 px-5 py-3 rounded-lg shadow-2xl z-50 cursor-pointer max-w-md"
    :class="{
      'bg-yellow-500 text-white': type === 'warning',
      'bg-red-500 text-white': type === 'error',
      'bg-green-500 text-white': type === 'success',
      'bg-blue-500 text-white': type === 'info'
    }">
    <div class="flex items-center gap-3">
      <svg x-show="type === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
      </svg>
      <svg x-show="type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
      </svg>
      <span x-text="message" class="flex-1"></span>
      <button @click="show = false" class="text-white hover:text-gray-200 font-bold">✕</button>
    </div>
  </div>

  @push('scripts')
  <script>
    document.addEventListener('livewire:initialized', () => {
      Livewire.on('stock-limit', (event) => {
        showToast(event.message || event[0]?.message, 'warning');
      });

      Livewire.on('checkout-error', (event) => {
        showToast(event.message || event[0]?.message, 'error');
      });

      Livewire.on('cart-updated', () => {
        showToast('Cart updated successfully', 'success');
      });

      function showToast(message, type = 'info') {
        const toastElement = document.querySelector('[x-data*="show"]');
        if (toastElement && toastElement.__x) {
          toastElement.__x.$data.message = message;
          toastElement.__x.$data.type = type;
          toastElement.__x.$data.show = true;
          setTimeout(() => {
            toastElement.__x.$data.show = false;
          }, 4000);
        }
      }
    });
  </script>
  @endpush
</section>