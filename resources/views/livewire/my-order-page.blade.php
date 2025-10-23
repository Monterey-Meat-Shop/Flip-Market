<div class="max-w-7xl mx-auto p-4 sm:p-6 bg-gray-50 min-h-screen">
  <!-- Success/Error Messages -->
  @if (session()->has('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {{ session('error') }}
      </div>
  @endif

  @if (session()->has('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
          {{ session('success') }}
      </div>
  @endif

  <!-- Header -->
  <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl mb-4">My Orders</h2>

  <!-- Tabs -->
  <div class="flex flex-wrap gap-6 text-md font-medium text-gray-600 mb-4 ">
    <button wire:click="setActiveTab('all')" 
            class="cursor-pointer pb-2 border-b-2 {{ $activeTab === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      All
    </button>
    <button wire:click="setActiveTab('to_pay')" 
            class="cursor-pointer pb-2 border-b-2 {{ $activeTab === 'to_pay' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      To pay
    </button>
    <button wire:click="setActiveTab('processing')" 
            class="pb-2 border-b-2 {{ $activeTab === 'processing' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      Processing
    </button>
    <button wire:click="setActiveTab('in_transit')" 
            class="pb-2 border-b-2 {{ $activeTab === 'in_transit' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      In transit
    </button>
    <button wire:click="setActiveTab('to_receive')" 
            class="cursor-pointer pb-2 border-b-2 {{ $activeTab === 'to_receive' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      To receive
    </button>
    <button wire:click="setActiveTab('completed')" 
            class="cursor-pointer pb-2 border-b-2 {{ $activeTab === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      Completed
    </button>
    <button wire:click="setActiveTab('returned')" 
            class="cursor-pointer pb-2 border-b-2 {{ $activeTab === 'returned' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:text-blue-600' }}">
      Returned
    </button>
  </div>

  <!-- Search Bar -->
  <div class="mb-4">
    <div class="relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m21 21-4.34-4.34" />
          <circle cx="11" cy="11" r="8" />
        </svg>
      </span>
      <input 
        type="text" 
        placeholder="Search your Order"
        wire:model.live="searchQuery"
        class="w-full pl-10 pr-4 py-4 border border-gray-300 rounded-md bg-gray-100 text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>
  </div>

  <!-- Orders List -->
  @if($orders->count() > 0)
    @foreach($orders as $order)
      @php
        $statusInfo = $this->getStatusColor($order);
        $statusBg = $statusInfo[0];
        $statusText = $statusInfo[1];
        $statusLabel = $statusInfo[2];
        $hasReturnRequest = $order->returnRequest !== null;
        $canReturn = in_array(strtolower($order->order_status), ['completed', 'delivered']) && !$hasReturnRequest;
      @endphp
      
      <!-- Order Item -->
      <div class="bg-white border border-gray-300 rounded-lg mb-4 h-50">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-4 py-2 border-b border-gray-300">
          <span class="font-medium text-lg text-gray-700">Order Id: #{{ $order->orderID }}</span>
          <div class="flex items-center gap-2 mt-2 sm:mt-0">
            <span class="text-sm font-medium text-gray-500">{{ $order->order_date->format('M d, Y') }}</span>
            <span class="px-3 py-2 {{ $statusBg }} {{ $statusText }} text-md font-medium rounded-full">
              {{ $statusLabel }}
            </span>
          </div>
        </div>

        @foreach($order->orderItems as $item)
        <div class="flex flex-col sm:flex-row sm:items-start sm:space-x-4 p-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
          <!-- Product Image -->
          <div class="shrink-0 mx-auto sm:mx-0">
            @if($item->product && $item->product->image_path)
              <img src="{{ asset('storage/' . $item->product->image_path) }}" 
                   alt="{{ $item->product->name }}"
                   class="w-28 h-28 object-cover rounded-md mx-2">
            @else
              <div class="w-24 h-24 bg-gray-200 rounded-md flex items-center justify-center">
                <span class="text-gray-500 text-xs font-semibold">
                  {{ strtoupper(substr($item->product->name ?? 'Product', 0, 2)) }}
                </span>
              </div>
            @endif
          </div>
          
          <!-- Product Details -->
          <div class="flex-1 mt-3 sm:mt-0">
            <h3 class="text-lg font-medium text-gray-800 mb-2">{{ $item->product->name ?? 'Product Name' }}</h3>
            @if($item->colorway || $item->size)
            <p class="text-md text-gray-500 mb-3">
              @if($item->colorway){{ $item->colorway }}@endif@if($item->colorway && $item->size) | @endif@if($item->size)Size: {{ $item->size }}@endif
            </p>
            @endif
            <div class="flex flex-wrap gap-2">
              @if($item->product->brand)
                <span class="text-md text-blue-600 border border-blue-400 rounded px-2 py-0.5">{{ $item->product->brand->name }}</span>
              @endif
              @if($item->product->category)
                <span class="text-md text-blue-600 border border-blue-400 rounded px-2 py-0.5">{{ $item->product->category->name }}</span>
              @endif
            </div>
          </div>

          <!-- Price and Actions -->
          <div class="text-right text-md text-gray-700 mt-4 sm:mt-0">
            <p class="font-medium">₱{{ number_format($item->sub_total, 2) }}</p>
            <p>Qty: {{ $item->quantity }}</p>
            
            @if($loop->last) <!-- Only show buttons on last item -->
            <div class="flex flex-col sm:flex-row sm:justify-end gap-2 mt-4">
              <button wire:click="viewOrderDetails({{ $order->orderID }})"
                      class="bg-blue-600 hover:bg-blue-800 text-white rounded-lg px-3 py-2 text-md">
                View Order Details
              </button>
              
              @if($order->order_status === 'pending')
                <button wire:click="cancelOrder({{ $order->orderID }})"
                        wire:confirm="Are you sure you want to cancel this order?"
                        class="bg-red-600 hover:bg-red-800 text-white rounded-lg px-3 py-2 text-md">
                  Cancel Order
                </button>
              @elseif($canReturn)
                <button wire:click="requestReturn({{ $order->orderID }})"
                        wire:confirm="Are you sure you want to request a return for this order?"
                        class="bg-orange-600 hover:bg-orange-800 text-white rounded-lg px-3 py-2 text-md">
                  Return Order
                </button>
              @elseif($hasReturnRequest)
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-yellow-600 font-medium">Return Status: {{ ucfirst($order->returnRequest->return_status) }}</span>
                  <a href="{{ route('returns.show', $order->returnRequest->returnID) }}" 
                     class="bg-gray-600 hover:bg-gray-800 text-white rounded-lg px-3 py-2 text-md text-center">
                    View Return
                  </a>
                </div>
              @endif

            </div>
            @endif
          </div>
        </div>
        @endforeach

        <!-- Order Summary -->
        @if($order->orderItems->count() > 1)
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
          <div class="flex justify-between">
            <span>{{ $order->orderItems->count() }} items</span>
            <span class="font-medium">Total: ₱{{ number_format($order->final_amount, 2) }}</span>
          </div>
        </div>
        @endif
      </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-6">
      {{ $orders->links() }}
    </div>

  @else
    <!-- No Orders Found -->
    <div class="bg-white border border-gray-300 rounded-lg p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
      <p class="mt-1 text-sm text-gray-500">
        @if($searchQuery)
          No orders match your search "{{ $searchQuery }}"
        @elseif($activeTab !== 'all')
          No orders found in this category
        @else
          You haven't placed any orders yet
        @endif
      </p>
      @if($searchQuery)
        <button wire:click="$set('searchQuery', '')" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
          Clear search
        </button>
      @else
        <a href="/" class="mt-2 inline-block text-blue-600 hover:text-blue-800 text-sm">
          Start shopping →
        </a>
      @endif
    </div>
  @endif
</div>