<div class="max-w-7xl mx-auto p-4 sm:p-6 bg-gray-50 min-h-screen">
  <!-- Steps -->
  <div class="flex justify-center">
    <div class="flex justify-between items-center mb-4 max-w-lg w-full">
      <div class="flex justify-center items-center space-x-2">
        <div class="w-6 h-6 flex items-center justify-center bg-blue-600 text-white rounded-full text-sm">1</div>
        <span class="text-sm font-medium text-blue-600">Return reason</span>
      </div>
      <div class="h-px flex-1 bg-gray-200 mx-2"></div>
      <span class="text-sm text-gray-400">Confirmation</span>
    </div>
  </div>

  <!-- Title -->
  <div class="my-5">
    <h2 class="text-xl font-semibold mb-1">Select the reason for returning:</h2>
    <p class="text-gray-500 text-md">To help us solve your request as quickly as possible, please answer the following questions.</p>
  </div>

  <!-- Error Messages -->
  @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <span class="block sm:inline">{{ session('error') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Selected Product -->
  @if($order)
  <div class="bg-white border border-gray-300 rounded-lg my-5 p-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Select Items to Return</h3>
      <p class="text-sm text-gray-500">Order #{{ $order->orderID }}</p>
    </div>
    
    <!-- Items List with Selection -->
    <div class="space-y-3">
      @foreach($order->orderItems as $item)
        <div class="border {{ in_array($item->orderItemID, $selectedItems) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} rounded-lg p-3">
          <div class="flex items-start space-x-3">
            <!-- Checkbox -->
            <div class="pt-1">
              <input type="checkbox" 
                     wire:click="toggleItem({{ $item->order_itemID }})"
                     {{ in_array($item->order_itemID, $selectedItems) ? 'checked' : '' }}
                     class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 cursor-pointer"/>
            </div>
            
            <!-- Product Image -->
            @if($item->product && $item->product->image_path)
              <img src="{{ asset('storage/' . $item->product->image_path) }}" 
                   alt="{{ $item->product->name }}"
                   class="w-16 h-16 object-cover rounded">
            @else
              <div class="w-16 h-16 bg-gray-200 rounded"></div>
            @endif
            
            <!-- Product Details -->
            <div class="flex-1">
              <h4 class="font-medium text-gray-900">{{ $item->product->name ?? 'Product Name' }}</h4>
              
              @if($item->productVariant)
                <div class="text-sm text-gray-600 mt-1">
                  @if($item->productVariant->colorway)
                    <span>Color: {{ $item->productVariant->colorway }}</span>
                  @endif
                  @if($item->productVariant->size)
                    <span class="ml-2">Size: {{ $item->productVariant->size }}</span>
                  @endif
                </div>
              @endif
              
              <p class="text-sm text-gray-500 mt-1">Unit Price: ₱{{ number_format($item->sub_total / $item->quantity, 2) }}</p>
              
              <!-- Quantity Selector (only if selected) -->
              @if(in_array($item->orderItemID, $selectedItems))
                <div class="mt-2 flex items-center space-x-2">
                  <label class="text-sm font-medium text-gray-700">Return Qty:</label>
                  <select 
                    wire:model="itemQuantities.{{ $item->orderItemID }}"
                    class="border border-gray-300 rounded px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500"
                  >
                    @for($i = 1; $i <= $item->quantity; $i++)
                      <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                  </select>
                  <span class="text-sm text-gray-500">of {{ $item->quantity }}</span>
                </div>
              @endif
            </div>
            
            <!-- Price -->
            <div class="text-right">
              <p class="font-semibold text-gray-900">₱{{ number_format($item->sub_total, 2) }}</p>
              <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    
    <!-- Selection Summary -->
    @if(count($selectedItems) > 0)
      <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex justify-between items-center">
          <span class="text-sm font-medium text-gray-700">
            {{ count($selectedItems) }} item(s) selected • 
            Total return qty: {{ collect($selectedItems)->sum(function($itemId) use ($itemQuantities) {
              return $itemQuantities[$itemId] ?? 0;
            }) }}
          </span>
          <span class="text-lg font-bold text-blue-600">
            Est. Refund: ₱{{ number_format(
              $order->orderItems->whereIn('orderItemID', $selectedItems)->sum(function($item) use ($itemQuantities) {
                $qty = $itemQuantities[$item->orderItemID] ?? $item->quantity;
                return ($item->sub_total / $item->quantity) * $qty;
              }), 2
            ) }}
          </span>
        </div>
      </div>
    @endif
  </div>
  @endif

  <!-- Return Form -->
  <form id="returnForm" wire:submit.prevent="submitReturn" enctype="multipart/form-data">
    @csrf

    <!-- Reason Selection -->
    <div class="grid grid-cols-1">
      <!-- Left side: Product condition -->
      <div class="space-y-2">
        <h3 class="font-medium text-gray-900 mb-2 text-lg">What is the condition of the product?</h3>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" wire:model="condition" value="not_delivered" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)" required>
          <span class="text-gray-700 text-md">The product was not delivered</span>
        </label>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" wire:model="condition" value="defective" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)">
          <span class="text-gray-700 text-md">Defective or Damaged Product</span>
        </label>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" wire:model="condition" value="changed_mind" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)">
          <span class="text-gray-700 text-md">Changed Mind/Not as Expected</span>
        </label>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" wire:model="condition" value="incorrect" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)">
          <span class="text-gray-700 text-md">Incorrect Product Received</span>
        </label>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" wire:model="condition" value="other" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(true)">
          <span class="text-gray-700 text-md">Other Reason:</span>
        </label>

        <textarea id="otherReasonTextarea" wire:model="other_reason" class="bg-blue-50 w-full p-4 rounded-lg text-sm text-gray-700 mt-2 hidden" placeholder="Kindly select your reasons for returning the product thoughtfully, as this will aid us in expediting your request resolution and ensuring your utmost satisfaction with the overall purchase experience."></textarea>
        
        @error('condition')
          <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
        @enderror
        
        @error('other_reason')
          <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
        @enderror
        
        <div id="error-message" class="text-red-600 text-sm mt-2 hidden"></div>
      </div>
    </div>

    <div class="my-5">
      <h3 class="text-lg font-medium text-gray-900 my-2">Product Image*</h3>
      <div class="flex items-center justify-center w-full">
        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-white hover:bg-gray-100 overflow-hidden relative">
          
          @if ($product_image)
            <!-- Preview uploaded image -->
            <img src="{{ $product_image->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-contain" alt="Preview">
          @else
            <!-- Upload instructions -->
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
              <svg class="w-8 h-8 mb-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.069 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
              </svg>
              <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
              <p class="text-xs text-gray-500">PNG, JPG or GIF (MAX. 2MB)</p>
              <div wire:loading wire:target="product_image" class="mt-2">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </div>
          @endif

          <input id="dropzone-file" wire:model="product_image" type="file" accept="image/*" class="hidden" />
        </label>
      </div>
      
      @if ($product_image)
        <div class="mt-2 flex items-center justify-between text-sm text-gray-600">
          <span>{{ $product_image->getClientOriginalName() }}</span>
          <button type="button" wire:click="$set('product_image', null)" class="text-red-600 hover:text-red-800">Remove</button>
        </div>
      @endif
      
      @error('product_image')
        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
      @enderror
    </div>

    <!-- Buttons -->
    <div class="flex justify-between mt-5">
      <a href="{{ route('my.orders') }}" class="px-4 py-2 border border-red-700 rounded-lg text-red-600 hover:bg-red-500 hover:text-white cursor-pointer transition-colors inline-block text-center">Cancel Return</a>
      <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 cursor-pointer transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" wire:loading.attr="disabled">
        <span wire:loading.remove>Return the Product</span>
        <span wire:loading>
          <svg class="animate-spin inline-block h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
      </button>
    </div>
  </form>
</div>

<script>
  function toggleTextarea(show) {
    const textarea = document.getElementById('otherReasonTextarea');
    if (show) {
      textarea.classList.remove('hidden');
    } else {
      textarea.classList.add('hidden');
    }
  }
  
  // Listen for Livewire updates to condition
  document.addEventListener('livewire:initialized', () => {
    Livewire.on('condition-changed', (value) => {
      toggleTextarea(value === 'other');
    });
  });
</script>