<div class="min-h-screen bg-gray-100 p-6">
  <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Success/Error Messages --}}
    @if (session()->has('error'))
        <div class="lg:col-span-3 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('order_success'))
        <div class="lg:col-span-3 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('order_success') }}
        </div>
    @endif

    {{-- LEFT: Forms --}}
    <div class="lg:col-span-2 space-y-6">
      
      {{-- Billing Address --}}
      <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-lg font-semibold mb-4">Billing address</h2>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm mb-1">First Name</label>
            <input type="text" wire:model="firstName"
                   class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                          focus:border-blue-500 focus:ring-blue-500 @error('firstName') border-red-500 @enderror">
            @error('firstName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>
          <div>
            <label class="block text-sm mb-1">Last Name</label>
            <input type="text" wire:model="lastName"
                   class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                          focus:border-blue-500 focus:ring-blue-500 @error('lastName') border-red-500 @enderror">
            @error('lastName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm mb-1">Phone Number</label>
          <input type="text" wire:model="phone"
                 class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                        focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
          @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Address Selection --}}
        <div class="mb-4">
          <label class="block text-sm mb-1">Select Delivery Address</label>
          @if($availableAddresses->isNotEmpty())
            <select wire:model.live="selectedAddressId"
                    class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                           focus:border-blue-500 focus:ring-blue-500 @error('selectedAddressId') border-red-500 @enderror">
              <option value="">Choose an address...</option>
              @foreach($availableAddresses as $index => $address)
                <option value="{{ $address->addressID }}">
                  Address {{ $index + 1 }}: {{ $address->address_line_1 }}, {{ $address->city }}, {{ $address->province }}
                </option>
              @endforeach
            </select>
            @error('selectedAddressId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          @else
            <div class="w-full py-2 px-2 rounded-lg border border-gray-300 bg-gray-50 text-gray-500">
              No saved addresses found. Please add an address first.
            </div>
            <a href="{{ route('my.account') }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
              Add a new address →
            </a>
          @endif
        </div>

        {{-- Selected Address Preview --}}
        @if($selectedAddressId && $addressLine1)
          <div class="bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Selected Address:</h3>
            <div class="text-sm text-gray-600 space-y-1">
              <p><strong>Address:</strong> {{ $addressLine1 }}</p>
              <p><strong>City:</strong> {{ $city }}</p>
              <p><strong>Province:</strong> {{ $province }}</p>
              <p><strong>Postal Code:</strong> {{ $postalCode }}</p>
            </div>
          </div>
        @endif
      </div>

      {{-- Payment Methods --}}
      <div class="bg-white p-6 rounded-2xl shadow">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Select Payment Method</h3>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          @foreach($paymentMethods as $method)
            <div class="flex items-start rounded-lg border border-gray-200 bg-gray-50 p-4
                        {{ $selectedPaymentMethod == $method->payment_methodID ? 'border-blue-500 bg-blue-50' : '' }}">
              <input id="payment-{{ $method->payment_methodID }}"
                     type="radio"
                     name="payment-method"
                     value="{{ $method->payment_methodID }}"
                     wire:model.live="selectedPaymentMethod"
                     class="h-4 w-4 border-gray-300" />

              <div class="ms-4 text-sm">
                <label for="payment-{{ $method->payment_methodID }}" class="font-medium leading-none text-gray-900">
                  {{ $method->method_name }}
                </label>
                <p class="mt-1 text-xs text-gray-500">
                  @if(strtolower($method->method_name) === 'gcash')
                    Pay using your GCash wallet
                  @elseif(strtolower($method->method_name) === 'bank transfer' || strtolower($method->method_name) === 'banktransfer')
                    Transfer to our bank account
                  @else
                    {{ $method->method_name }}
                  @endif
                </p>
              </div>
            </div>
          @endforeach
        </div>
        @error('selectedPaymentMethod') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror

        {{-- GCash Instructions --}}
        @if($showGcashReference)
          <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-3">GCash Payment Instructions</h4>

            <div class="grid md:grid-cols-2 gap-4 mb-4">
              <div class="text-center">
                <p class="text-sm text-gray-600 mb-2">Scan QR Code:</p>
                <div class="bg-white p-3 rounded-lg inline-block">
                  <img src="{{ asset('storage/payment_qr/gcash.png') }}"
                       alt="GCash QR Code"
                       class="w-48 h-48 object-contain mx-auto">
                </div>
              </div>

              <div class="space-y-2 text-sm">
                <div>
                  <p class="text-gray-600">Account Name:</p>
                  <p class="font-semibold">Flip Market</p>
                </div>
                <div>
                  <p class="text-gray-600">GCash Number:</p>
                  <p class="font-semibold">0917-123-4567</p>
                </div>
                <div>
                  <p class="text-gray-600">Amount to Pay:</p>
                  <p class="font-semibold text-lg text-blue-600">₱{{ number_format($totalAmount, 2) }}</p>
                </div>
              </div>
            </div>

            <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
              <p class="text-xs text-gray-700">
                <strong>Note:</strong> Please send the exact amount and enter the reference number below after payment.
              </p>
            </div>

            <label class="block text-sm mb-1 font-medium">
              GCash Reference Number <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="gcashReferenceNumber"
                   class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                          focus:border-blue-500 focus:ring-blue-500 @error('gcashReferenceNumber') border-red-500 @enderror"
                   placeholder="Enter the 13-digit reference number from GCash">
            @error('gcashReferenceNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>
        @endif

        {{-- Bank Transfer Instructions --}}
        @if($showBankTransferReference)
          <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-3">Bank Transfer Payment Instructions</h4>

            <div class="grid md:grid-cols-2 gap-4 mb-4">
              <div class="text-center">
                <p class="text-sm text-gray-600 mb-2">Scan QR Code:</p>
                <div class="bg-white p-3 rounded-lg inline-block">
                  <img src="{{ asset('storage/payment_qr/gcash.png') }}"
                       alt="Bank QR Code"
                       class="w-48 h-48 object-contain mx-auto">
                </div>
              </div>

              <div class="space-y-2 text-sm">
                <div>
                  <p class="text-gray-600">Bank Name:</p>
                  <p class="font-semibold">BDO / BPI / Metrobank</p>
                </div>
                <div>
                  <p class="text-gray-600">Account Name:</p>
                  <p class="font-semibold">Flip Market</p>
                </div>
                <div>
                  <p class="text-gray-600">Account Number:</p>
                  <p class="font-semibold">1234-5678-9012</p>
                </div>
                <div>
                  <p class="text-gray-600">Amount to Pay:</p>
                  <p class="font-semibold text-lg text-green-600">₱{{ number_format($totalAmount, 2) }}</p>
                </div>
              </div>
            </div>

            <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
              <p class="text-xs text-gray-700">
                <strong>Note:</strong> Please send the exact amount and enter the reference number from your bank receipt below.
              </p>
            </div>

            <label class="block text-sm mb-1 font-medium">
              Bank Transfer Reference Number <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="bankTransferReferenceNumber"
                   class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                          focus:border-blue-500 focus:ring-blue-500 @error('bankTransferReferenceNumber') border-red-500 @enderror"
                   placeholder="Enter your bank transfer reference number">
            @error('bankTransferReferenceNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>
        @endif
      </div>

      {{-- Shipping Methods --}}
      <div class="bg-white p-6 rounded-2xl shadow">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Select Shipping Method</h3>

        <div class="grid grid-cols-1 gap-4">
          @foreach($availableShippingMethods as $methodKey => $method)
            <div class="flex flex-col rounded-lg border border-gray-200 bg-gray-50 p-4
                        {{ $selectedShippingMethod == $methodKey ? 'border-blue-500 bg-blue-50' : '' }}">
              <div class="flex items-start">
                <input id="shipping-{{ $methodKey }}"
                       type="radio"
                       name="shipping-method"
                       value="{{ $methodKey }}"
                       wire:model.live="selectedShippingMethod"
                       class="h-4 w-4 border-gray-300 mt-1" />

                <div class="ms-4 flex-1">
                  <div class="flex justify-between items-start">
                    <div>
                      <label for="shipping-{{ $methodKey }}" class="font-medium leading-none text-gray-900">
                        {{ $method['name'] }}
                      </label>
                      <p class="mt-1 text-xs text-gray-500">{{ $method['description'] }}</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                      ₱{{ number_format($method['fee'], 2) }}
                    </span>
                  </div>
                </div>
              </div>

              {{-- 🔹 Lalamove extra UI --}}
              @if($methodKey === 'LALAMOVE' && $selectedShippingMethod === 'LALAMOVE')
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 space-y-3">
                  <p class="text-sm font-semibold text-gray-800">
                    You selected Lalamove delivery.
                  </p>
                  <p class="text-xs text-gray-700">
                    Delivery fee depends on your pickup and drop-off location. Choose who will create the Lalamove booking:
                  </p>

                  <div class="flex flex-wrap gap-3 text-sm">
                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border
                                  cursor-pointer {{ $lalamoveBookingOption === 'customer' ? 'border-blue-500 bg-white' : 'border-gray-300 bg-gray-50' }}">
                      <input type="radio"
                             value="customer"
                             wire:model.live="lalamoveBookingOption"
                             class="h-4 w-4">
                      <span>I will book Lalamove myself (pay driver directly).</span>
                    </label>

                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border
                                  cursor-pointer {{ $lalamoveBookingOption === 'store' ? 'border-blue-500 bg-white' : 'border-gray-300 bg-gray-50' }}">
                      <input type="radio"
                             value="store"
                             wire:model.live="lalamoveBookingOption"
                             class="h-4 w-4">
                      <span>Store will book Lalamove for me (fee added to this order).</span>
                    </label>
                  </div>

                  {{-- If customer will book themselves: show store pickup + tracking input --}}
                  @if($lalamoveBookingOption === 'customer')
                    <div class="mt-3 bg-white border border-yellow-100 rounded p-3 text-xs text-gray-700 space-y-1">
                      <p class="font-semibold text-gray-800">Pickup details for your Lalamove booking:</p>
                      <p><strong>Store Address:</strong> Holy Spirit QC</p>
                      <p><strong>Store Mobile Number:</strong> 09933367891</p>
                      <p class="text-[11px] text-gray-500">
                        Use these details as the pickup information in your Lalamove app. You will pay the Lalamove rider directly.
                      </p>
                    </div>

                    <div class="mt-3">
                      <label class="block text-xs font-semibold text-gray-800 mb-1">
                        Lalamove Tracking Number or Link (optional)
                      </label>
                      <input type="text"
                             wire:model="lalamoveTracking"
                             class="w-full py-2 px-2 rounded-lg border border-gray-300 text-gray-800
                                    focus:border-blue-500 focus:ring-blue-500"
                             placeholder="Paste your Lalamove tracking number or tracking URL here">
                      @error('lalamoveTracking') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                  @endif

                  {{-- If store will book: just info, no fee input, no tracking input --}}
                  @if($lalamoveBookingOption === 'store')
                    <p class="mt-1 text-xs text-gray-700">
                      Our staff will arrange the Lalamove booking for you. The final delivery fee will be confirmed and
                      reflected in your order or communicated to you.
                    </p>
                  @endif
                </div>
              @endif
            </div>
          @endforeach
        </div>

        @error('selectedShippingMethod')
          <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span>
        @enderror
      </div>
    </div>

    {{-- RIGHT: Summary --}}
    <div class="space-y-6">
      {{-- Cart Items --}}
      @foreach($cartItems as $item)
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex items-center gap-4">
          <a href="#" class="shrink-0">
            @if($item->product && $item->product->image_path)
              <img src="{{ asset('storage/' . $item->product->image_path) }}"
                   alt="{{ $item->product->name }}"
                   class="h-20 w-20 object-cover rounded">
            @else
              <div class="h-20 w-20 bg-gray-200 rounded flex items-center justify-center">
                <span class="text-gray-500 text-xs font-semibold">
                  {{ strtoupper(substr($item->product->name, 0, 2)) }}
                </span>
              </div>
            @endif
          </a>

          <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-800 leading-tight">
              {{ $item->product->name }}
            </h3>
            <p class="mt-1 text-xs text-gray-600 leading-snug">
              @if($item->size) Size: {{ $item->size }} @endif
              @if($item->colorway) {{ $item->size ? '|' : '' }} Color: {{ $item->colorway }} @endif
            </p>

            @if(isset($item->active_discount) && $item->active_discount)
              <span class="inline-block mt-1 text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">
                {{ $item->active_discount->discount_value }}{{ $item->active_discount->discount_type === 'Percentage' ? '%' : '₱' }} OFF
              </span>
            @endif

            @if(isset($item->original_price) && $item->original_price)
              <div class="mt-1">
                <span class="text-xs text-gray-400 line-through">
                  ₱{{ number_format($item->original_price, 2) }}
                </span>
                <span class="text-xs font-semibold text-blue-600 ml-1">
                  ₱{{ number_format($item->unit_price, 2) }}
                </span>
              </div>
            @endif
          </div>

          <div class="flex flex-col items-center justify-center w-16">
            <span class="text-sm text-gray-700">Qty: {{ $item->quantity }}</span>
          </div>

          <div class="flex flex-col items-end justify-between shrink-0">
            <span class="text-sm font-semibold text-gray-900">
              ₱{{ number_format($item->sub_total, 2) }}
            </span>
          </div>
        </div>
      @endforeach

      {{-- Order Summary --}}
      <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-lg font-semibold mb-4">Order summary</h2>

        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Subtotal ({{ $cartCount }} items)</span>
            <span class="text-sm font-medium">₱{{ number_format($subtotal, 2) }}</span>
          </div>

          @if($productDiscountSavings > 0)
            <div class="flex justify-between text-green-600">
              <span class="text-sm">Product Discounts</span>
              <span class="text-sm font-semibold">-₱{{ number_format($productDiscountSavings, 2) }}</span>
            </div>
          @endif

          @if($discountAmount > 0)
            <div class="flex justify-between text-green-600">
              <span class="text-sm">Coupon Discount</span>
              <span class="text-sm font-semibold">-₱{{ number_format($discountAmount, 2) }}</span>
            </div>
          @endif

          <div class="flex justify-between">
            <span class="text-sm text-gray-600">
              Delivery Fee
              @if($selectedShippingMethod)
                <span class="text-xs text-gray-500">
                  ({{ $availableShippingMethods[$selectedShippingMethod]['name'] }})
                </span>
              @endif
            </span>
            <span class="text-sm font-medium">
              ₱{{ number_format($deliveryFee, 2) }}
            </span>
          </div>

          @php
            $totalSavings = $productDiscountSavings + $discountAmount;
          @endphp

          @if($totalSavings > 0)
            <div class="border-t pt-2">
              <div class="flex justify-between text-green-700 bg-green-50 p-2 rounded">
                <span class="text-sm font-medium">Total Savings</span>
                <span class="text-sm font-bold">₱{{ number_format($totalSavings, 2) }}</span>
              </div>
            </div>
          @endif
        </div>

        <div class="flex justify-between font-bold text-lg border-t pt-4 mt-4">
          <span>Total</span>
          <span class="text-blue-700">₱{{ number_format($totalAmount, 2) }}</span>
        </div>

        <button wire:click="placeOrder"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="w-full mt-4 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-800 cursor-pointer disabled:opacity-50 transition">
          <span wire:loading.remove>Place Order</span>
          <span wire:loading>Processing...</span>
        </button>

        <a href="{{ route('cart') }}"
           class="block text-center text-blue-600 mt-4 underline hover:no-underline">
          Return to Cart
        </a>
      </div>

      {{-- Upload Screenshot --}}
      <div class="bg-white p-6 rounded-2xl shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Payment Screenshot</h3>

        <p class="text-sm text-gray-600 mb-3">
          Please upload a screenshot of your payment as proof.
        </p>

        <input type="file" required
               wire:model="paymentScreenshot"
               accept="image/*"
               class="w-full text-sm text-gray-800 border border-gray-300 rounded-lg p-2
                      focus:ring-blue-500 focus:border-blue-500" />

        @error('paymentScreenshot')
          <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
        @enderror

        @if ($paymentScreenshot)
          <div class="mt-4 text-center">
            <p class="text-sm text-gray-700 mb-2 font-medium">Preview:</p>
            <img src="{{ $paymentScreenshot->temporaryUrl() }}"
                 alt="Screenshot Preview"
                 class="mx-auto w-48 h-48 object-cover rounded-lg border shadow">
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
