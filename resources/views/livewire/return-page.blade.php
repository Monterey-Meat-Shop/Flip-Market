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
    <h2 class="text-xl font-semibold mb-1"> Select the reason for returning:</h2>
    <p class="text-gray-500 text-md">To help us solve your request as quickly as possible, please answer the following questions.</p>
  </div>

  <!-- Selected Product -->
  <div class="flex justify-between items-center p-4 border border-gray-300 rounded-lg bg-gray-50 my-5">
    <div class="flex items-center space-x-4">
      <img class="w-10 h-10 bg-gray-200 rounded"></img>
      <div>
        <p class="font-medium text-gray-900 text-lg">PC system All in One APPLE iMac (2023) Apple M3, 24" Retina 4.5K, 8GB, SSD 256GB, 10-core GPU, Silver</p>
      </div>
    </div>
    <div class="text-right text-md text-gray-500">
      <p>Order Number: <span class="font-medium">#737423642</span></p>
    </div>
  </div>

  <!-- Reason Selection -->
  <div class="grid grid-cols-1">
    <!-- Left side: Product condition -->
    <div class="space-y-2">
      <h3 class="font-medium text-gray-900 mb-2 text-lg">What is the condition of the product?</h3>

      <label class="flex items-center space-x-2">
        <input type="radio" name="condition" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"    onclick="toggleTextarea(false)"
>
        <span class="text-gray-700 text-md">The product was not delivered</span>
      </label>

        <label class="flex items-center space-x-2">
        <input type="radio" name="condition" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"    onclick="toggleTextarea(false)"
>
        <span class="text-gray-700 text-md">Defective or Damaged Product</span>
      </label>

        <label class="flex items-center space-x-2">
        <input type="radio" name="condition" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"    onclick="toggleTextarea(false)"
>
        <span class="text-gray-700 text-md">Changed Mind/Not as Expected</span>
      </label>


        <label class="flex items-center space-x-2">
        <input type="radio" name="condition" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"     onclick="toggleTextarea(false)"
> 
        <span class="text-gray-700 text-md">Incorrect Product Received</span>
      </label>


        <label class="flex items-center space-x-2">
            <input type="radio" name="condition" value="other" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"onclick="toggleTextarea(true)">
            <span class="text-gray-700 text-md">Other Reason: </span>
        </label>

       <textarea id="otherReasonTextarea" class="bg-blue-50 w-full p-4 rounded-lg text-sm text-gray-700 mt-2 hidden"
        placeholder="Kindly select your reasons for returning the product thoughtfully, as this will aid us in expediting your request resolution and ensuring your utmost satisfaction with the overall purchase experience.">
        </textarea>
    </div>
</div>


<div class="my-5">
    
<h3 class="text-lg font-medium  text-gray-900 my-2">Product Image*</h3>
<div class="flex items-center justify-center w-full">
  <label for="dropzone-file" 
    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 overflow-hidden">
    

    <div id="upload-instructions" class="flex flex-col items-center justify-center pt-5 pb-6">
      <svg class="w-8 h-8 mb-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.069 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
      </svg>
      <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
      <p class="text-xs text-gray-500">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
    </div>

    <input id="dropzone-file" type="file" accept="image/*" class="hidden" />
  </label>
</div>

</div>



  <!-- Buttons -->
  <div class="flex justify-between mt-5">
    <button class="px-4 py-2 border border-red-700 rounded-lg text-red-600 hover:bg-red-500 hover:text-white cursor-pointer">Cancel Return</button>
    <button class="px-6 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 cursor-pointer">Return the Product</button>
  </div>
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
  
</script>