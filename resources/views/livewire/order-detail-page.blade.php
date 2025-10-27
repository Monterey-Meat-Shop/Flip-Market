<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto min-h-screen">
    <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl">Order Details</h2>

    <!-- Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-5">
      <!-- Customer Card -->
      <div class="flex flex-col bg-white border border-gray-200 shadow-lg rounded-xl">
        <div class="p-4 md:p-5 flex gap-x-4">
          <div class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg">
            <svg class="flex-shrink-0 size-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
          </div>
          <div class="grow">
            <div class="flex items-center gap-x-2">
              <p class="text-xs uppercase tracking-wide text-gray-500">Customer</p>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
              <div>{{ $order->customer->first_name }} {{ $order->customer->last_name }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Date Card -->
      <div class="flex flex-col bg-white border border-gray-200 shadow-lg rounded-xl">
        <div class="p-4 md:p-5 flex gap-x-4">
          <div class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg">
            <svg class="flex-shrink-0 size-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 22h14" />
              <path d="M5 2h14" />
              <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
              <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
            </svg>
          </div>
          <div class="grow">
            <div class="flex items-center gap-x-2">
              <p class="text-xs uppercase tracking-wide text-gray-500">Order Date</p>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
              <h3 class="text-xl font-medium text-gray-800">{{ $order->order_date->format('d-m-Y') }}</h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Status Card -->
      <div class="flex flex-col bg-white border border-gray-200 shadow-lg rounded-xl">
        <div class="p-4 md:p-5 flex gap-x-4">
          <div class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg">
            <svg class="flex-shrink-0 size-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" />
              <path d="m12 12 4 10 1.7-4.3L22 16Z" />
            </svg>
          </div>
          <div class="grow">
            <div class="flex items-center gap-x-2">
              <p class="text-xs uppercase tracking-wide text-gray-500">Order Status</p>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
              @php
                $statusColors = [
                  'Pending' => 'bg-orange-500',
                  'Processing' => 'bg-yellow-500',
                  'Confirmed' => 'bg-blue-500',
                  'Shipped' => 'bg-purple-500',
                  'Completed' => 'bg-green-500',
                  'Delivered' => 'bg-green-500',
                  'Cancelled' => 'bg-red-500',
                ];
                $statusClass = $statusColors[$order->order_status] ?? 'bg-gray-500';
              @endphp
              <span class="{{ $statusClass }} py-1 px-3 rounded text-white shadow">{{ $order->order_status }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Status Card -->
      <div class="flex flex-col bg-white border border-gray-200 shadow-lg rounded-xl">
        <div class="p-4 md:p-5 flex gap-x-4">
          <div class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg">
            <svg class="flex-shrink-0 size-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z" />
              <path d="M12 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
              <path d="M21 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2" />
              <path d="M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2" />
            </svg>
          </div>
          <div class="grow">
            <div class="flex items-center gap-x-2">
              <p class="text-xs uppercase tracking-wide text-gray-500">Payment Status</p>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
              @php
                $paymentStatusColors = [
                  'paid' => 'bg-green-500',
                  'unpaid' => 'bg-red-500',
                  'pending' => 'bg-yellow-500',
                  'cash_on_delivery' => 'bg-blue-500',
                ];
                $paymentClass = $paymentStatusColors[strtolower($order->payment->status ?? 'unpaid')] ?? 'bg-gray-500';
              @endphp
              <span class="{{ $paymentClass }} py-1 px-3 rounded text-white shadow capitalize">{{ $order->payment->status ?? 'Unpaid' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Grid -->

    <div class="flex flex-col md:flex-row gap-4 mt-4">
      <div class="md:w-3/4">
        <!-- Products Table -->
        <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4 border border-gray-200">
          <h2 class="text-lg font-semibold mb-4">Order Items</h2>
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="text-left font-semibold py-3">Product</th>
                <th class="text-left font-semibold py-3">Price</th>
                <th class="text-left font-semibold py-3">Quantity</th>
                <th class="text-left font-semibold py-3">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($order->orderItems as $item)
              @php
                // Check if item has discount based on stored data
                $hasDiscount = (!empty($item->original_price) && $item->original_price > $item->unit_price) 
                            || (!empty($item->discount_amount) && $item->discount_amount > 0);
                
                if ($hasDiscount) {
                    $originalPrice = !empty($item->original_price) 
                        ? $item->original_price 
                        : ($item->unit_price + $item->discount_amount);
                    $savings = $originalPrice - $item->unit_price;
                }
              @endphp
              <tr wire:key="{{ $item->order_itemID }}" class="border-b">
                <td class="py-4">
                  <div class="flex items-center">
                    @if($item->product && $item->product->image_path)
                      <img src="{{ asset('storage/' . $item->product->image_path) }}" 
                           alt="{{ $item->product->name }}"
                           class="h-16 w-16 mr-4 object-cover rounded">
                    @else
                      <div class="h-16 w-16 mr-4 bg-gray-200 rounded flex items-center justify-center">
                        <span class="text-gray-500 text-xs font-semibold">
                          {{ strtoupper(substr($item->product->name ?? 'Product', 0, 2)) }}
                        </span>
                      </div>
                    @endif
                    <div>
                      <span class="font-semibold">{{ $item->product->name }}</span>
                      @if($item->size || $item->colorway)
                        <p class="text-xs text-gray-500">
                          @if($item->colorway) {{ $item->colorway }} @endif
                          @if($item->size) | Size: {{ $item->size }} @endif
                        </p>
                      @endif

                      {{-- Show discount badge if item has discount --}}
                      @if($hasDiscount)
                        <div class="flex items-center gap-2 mt-1">
                          <span class="inline-block text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded">
                            Discounted
                          </span>
                          @if(!empty($item->discount_name))
                            <span class="text-xs text-gray-500">
                              ({{ $item->discount_name }})
                            </span>
                          @endif
                        </div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="py-4">
                  <div>
                    {{-- Show original price if discounted --}}
                    @if($hasDiscount)
                      <span class="text-xs text-gray-400 line-through block">₱{{ number_format($originalPrice, 2) }}</span>
                      <span class="font-semibold text-green-600">₱{{ number_format($item->unit_price, 2) }}</span>
                      <span class="text-xs text-green-600 block">Save ₱{{ number_format($savings, 2) }}</span>
                    @else
                      <span class="font-semibold">₱{{ number_format($item->unit_price, 2) }}</span>
                    @endif
                  </div>
                </td>
                <td class="py-4">
                  <span class="text-center w-8">{{ $item->quantity }}</span>
                </td>
                <td class="py-4">
                  <span class="font-semibold">₱{{ number_format($item->sub_total, 2) }}</span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Shipping Address -->
        <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4 border border-gray-200">
          <h1 class="text-lg font-semibold text-gray-900 sm:text-lg mb-4">Shipping Address</h1>
          <div class="flex justify-between items-start">
            <div>
              <p class="font-medium">{{ $order->address_choice }}</p>
              <p class="text-gray-600">{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
            </div>
            <div>
              <p class="font-semibold">Phone:</p>
              <p>{{ $order->customer->phone }}</p>
            </div>
          </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4 border border-gray-200">
          <h1 class="text-lg font-semibold text-gray-900 sm:text-lg mb-4">Payment Information</h1>
          
          <div class="space-y-3">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm text-gray-600">Payment Method:</p>
                <p class="font-medium">{{ $order->payment->paymentMethod->method_name ?? 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Status:</p>
                <p class="font-medium capitalize">{{ $order->payment->status ?? 'Unpaid' }}</p>
              </div>
            </div>

            @if($order->payment && $order->payment->reference_number)
            <div class="border-t pt-3">
              <p class="text-sm text-gray-600 mb-1">Reference Number:</p>
              <p class="font-medium text-blue-600">{{ $order->payment->reference_number }}</p>
            </div>
            @endif

            @if($order->payment && $order->payment->screenshot_path)
            <div class="border-t pt-3">
              <p class="text-sm text-gray-600 mb-2">Payment Screenshot:</p>
              <div class="bg-gray-50 p-3 rounded-lg inline-block">
                <img src="{{ asset('storage/' . $order->payment->screenshot_path) }}" 
                     alt="Payment Screenshot"
                     class="max-w-xs max-h-64 object-contain rounded shadow-md cursor-pointer hover:scale-105 transition"
                     onclick="window.open(this.src, '_blank')">
              </div>
              <p class="text-xs text-gray-500 mt-2">Click image to view full size</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Summary Sidebar -->
      <div class="md:w-1/4">
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
          
          <div class="space-y-2">
            {{-- Original Subtotal (if there are discounts) --}}
            @if($productDiscountSavings > 0)
            <div class="flex justify-between text-sm text-gray-500">
              <span>Original Subtotal</span>
              <span class="line-through">₱{{ number_format($originalSubtotal, 2) }}</span>
            </div>
            @endif

            {{-- Current Subtotal --}}
            <div class="flex justify-between">
              <span>Subtotal</span>
              <span class="font-medium">₱{{ number_format($order->total_amount, 2) }}</span>
            </div>

            {{-- Product Discounts --}}
            @if($productDiscountSavings > 0)
            <div class="flex justify-between text-green-600">
              <span class="text-sm">Product Discounts</span>
              <span class="text-sm font-semibold">-₱{{ number_format($productDiscountSavings, 2) }}</span>
            </div>
            @endif

            {{-- Coupon Discount --}}
            @if($order->discount && $couponDiscountAmount > 0)
            <div class="flex justify-between text-green-600">
              <span class="text-sm">
                Coupon Discount
                @if($order->discount->name)
                  <span class="block text-xs text-gray-500">({{ $order->discount->name }})</span>
                @endif
              </span>
              <span class="text-sm font-semibold">-₱{{ number_format($couponDiscountAmount, 2) }}</span>
            </div>
            @endif

            {{-- Shipping --}}
            <div class="flex justify-between">
              <span>Shipping</span>
              <span>₱{{ number_format($order->shipping->shipping_fee ?? 0, 2) }}</span>
            </div>

            {{-- Total Savings --}}
            @php
              $totalSavings = $productDiscountSavings + $couponDiscountAmount;
            @endphp
            @if($totalSavings > 0)
            <div class="border-t pt-2 mt-2">
              <div class="flex justify-between bg-green-50 p-2 rounded text-green-700">
                <span class="text-sm font-medium">Total Savings</span>
                <span class="text-sm font-bold">₱{{ number_format($totalSavings, 2) }}</span>
              </div>
            </div>
            @endif

            {{-- Grand Total --}}
            <div class="border-t pt-3 mt-3">
              <div class="flex justify-between">
                <span class="font-bold text-lg">Grand Total</span>
                <span class="font-bold text-lg text-blue-700">₱{{ number_format($order->final_amount, 2) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('my.orders') }}" class="mt-4 block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition">
          Back to Orders
        </a>
      </div>
    </div>
</div>