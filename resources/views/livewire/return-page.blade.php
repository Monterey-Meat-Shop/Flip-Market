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
  <div class="flex justify-between items-center p-4 border border-gray-300 rounded-lg bg-white my-5">
    <div class="flex items-center space-x-4">
      @if($order->orderItems->first() && $order->orderItems->first()->product->image ?? null)
        <img src="{{ asset($order->orderItems->first()->product->image) }}" alt="Product" class="w-10 h-10 bg-gray-200 rounded object-cover">
      @else
        <div class="w-10 h-10 bg-gray-200 rounded"></div>
      @endif
      <div>
        <p class="font-medium text-gray-900 text-lg">{{ $order->orderItems->first()->product->name ?? 'Product Name' }}</p>
      </div>
    </div>
    <div class="text-right text-md text-gray-500">
      <p>Order Number: <span class="font-medium">#{{ $order->orderID }}</span></p>
    </div>
  </div>
  @endif

  <!-- Return Form -->
  <form id="returnForm" action="{{ route('returns.submit', $order->orderID ?? '') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Reason Selection -->
    <div class="grid grid-cols-1">
      <!-- Left side: Product condition -->
      <div class="space-y-2">
        <h3 class="font-medium text-gray-900 mb-2 text-lg">What is the condition of the product?</h3>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" name="condition" value="not_delivered" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)" {{ old('condition') == 'not_delivered' ? 'checked' : '' }} required>
          <span class="text-gray-700 text-md">The product was not delivered</span>
        </label>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" name="condition" value="defective" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)" {{ old('condition') == 'defective' ? 'checked' : '' }}>
          <span class="text-gray-700 text-md">Defective or Damaged Product</span>
        </label>

        {{-- <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" name="condition" value="changed_mind" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)" {{ old('condition') == 'changed_mind' ? 'checked' : '' }}>
          <span class="text-gray-700 text-md">Changed Mind/Not as Expected</span>
        </label> --}}

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" name="condition" value="incorrect" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(false)" {{ old('condition') == 'incorrect' ? 'checked' : '' }}>
          <span class="text-gray-700 text-md">Incorrect Product Received</span>
        </label>

        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="radio" name="condition" value="other" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" onclick="toggleTextarea(true)" {{ old('condition') == 'other' ? 'checked' : '' }}>
          <span class="text-gray-700 text-md">Other Reason:</span>
        </label>

        <textarea id="otherReasonTextarea" name="other_reason" class="bg-blue-50 w-full p-4 rounded-lg text-sm text-gray-700 mt-2 {{ old('condition') == 'other' ? '' : 'hidden' }}" placeholder="Kindly select your reasons for returning the product thoughtfully, as this will aid us in expediting your request resolution and ensuring your utmost satisfaction with the overall purchase experience.">{{ old('other_reason') }}</textarea>
        
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
          
          <div id="upload-instructions" class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-8 h-8 mb-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.069 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
            </svg>
            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
            <p class="text-xs text-gray-500">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
          </div>

          <img id="preview-image" class="hidden absolute inset-0 w-full h-full object-contain" alt="Preview">

          <input id="dropzone-file" name="product_image" type="file" accept="image/*" class="hidden" onchange="previewImage(event)" required />
        </label>
      </div>
      
      @error('product_image')
        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
      @enderror
    </div>

    <!-- Buttons -->
    <div class="flex justify-between mt-5">
      <a href="{{ route('my.orders') }}" class="px-4 py-2 border border-red-700 rounded-lg text-red-600 hover:bg-red-500 hover:text-white cursor-pointer transition-colors inline-block text-center">Cancel Return</a>
      <button type="submit" id="submitBtn" class="px-6 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 cursor-pointer transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
        <span id="buttonText">Return the Product</span>
        <span id="loadingSpinner" class="hidden">
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
      textarea.setAttribute('required', 'required');
    } else {
      textarea.classList.add('hidden');
      textarea.removeAttribute('required');
      textarea.value = '';
    }
  }

  function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview-image');
    const instructions = document.getElementById('upload-instructions');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        instructions.classList.add('hidden');
      };
      
      reader.readAsDataURL(input.files[0]);
    }
  }

  // Form validation and submission
  document.getElementById('returnForm').addEventListener('submit', function(e) {
    const errorMessage = document.getElementById('error-message');
    const conditionRadio = document.querySelector('input[name="condition"]:checked');
    const otherReasonTextarea = document.getElementById('otherReasonTextarea');
    const imageInput = document.getElementById('dropzone-file');
    
    errorMessage.classList.add('hidden');
    errorMessage.textContent = '';

    // Validate condition is selected
    if (!conditionRadio) {
      e.preventDefault();
      errorMessage.textContent = 'Please select a reason for the return.';
      errorMessage.classList.remove('hidden');
      return false;
    }

    // Validate "Other" reason has text
    if (conditionRadio.value === 'other' && !otherReasonTextarea.value.trim()) {
      e.preventDefault();
      errorMessage.textContent = 'Please provide details for "Other Reason".';
      errorMessage.classList.remove('hidden');
      otherReasonTextarea.focus();
      return false;
    }

    // Validate image is uploaded
    if (!imageInput.files || imageInput.files.length === 0) {
      e.preventDefault();
      errorMessage.textContent = 'Please upload a product image.';
      errorMessage.classList.remove('hidden');
      return false;
    }

    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    submitBtn.disabled = true;
    buttonText.classList.add('hidden');
    loadingSpinner.classList.remove('hidden');
  });
  
  // On page load, check if "other" was selected (for validation errors)
  document.addEventListener('DOMContentLoaded', function() {
    const otherRadio = document.querySelector('input[name="condition"][value="other"]');
    if (otherRadio && otherRadio.checked) {
      toggleTextarea(true);
    }
  });
</script>