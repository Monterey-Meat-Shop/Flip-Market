<section class="py-8 antialiased h-screen">
  <div class="mx-auto max-w-screen-xl px-4 lg:grid lg:grid-cols-12 lg:gap-8 mt-20">

    <!-- Left: Images -->
    <div class="lg:col-span-7">
      <div class="grid grid-cols-8 gap-4">
        
        <!-- Thumbnails -->
        <div class="flex flex-col gap-4 col-span-1">
          <button class="border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
            <img src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" class="h-12 object-contain" alt="">
          </button>
          <button class="border rounded-lg p-2">
            <img src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" class="h-12 object-contain" alt="">
          </button>
          <button class="border rounded-lg p-2">
            <img src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" class="h-12 object-contain" alt="">
          </button>
          <button class="border rounded-lg p-2">
            <img src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" class="h-12 object-contain" alt="">
          </button>
        </div>

        <!-- Main Image -->
        <div class="col-span-7">
          <img class="w-full rounded-lg object-contain" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" alt="Apple iMac 24" />
        </div>
      </div>


      <!-- Product details tabs -->
      <div class="mt-8">
        <h2 class="font-semibold">Product Details</h2>
        <p class="mt-2 text-sm text-gray-600">
          The product is a high-quality, durable solution designed to meet the needs of modern consumers. 
          It features advanced technology and ergonomic design for optimal performance and comfort.
        </p>
        <p class="mt-2 text-sm text-gray-600">
          Key features include a sleek interface, customizable settings, and compatibility with various devices. 
          It is ideal for professionals and enthusiasts alike.
        </p>
      </div>
    </div>

    <!-- Right: Product Info -->
    <div class="mt-6 lg:mt-0 lg:col-span-5">
      <div class="p-6 border rounded-lg">
        <h1 class="text-2xl font-bold">
          Apple iMac 24" All-In-One Computer, Apple M1, 8GB RAM
        </h1>

        <!-- Reviews -->
        <div class="flex items-center gap-2 mt-3">
          <span class="text-xs px-2.5 py-0.5 rounded bg-red-100 text-red-600">the last 2 products</span>
          <div class="flex text-yellow-400">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c..."/></svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c..."/></svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c..."/></svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c..."/></svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c..."/></svg>
          </div>
          <a href="#" class="text-sm text-blue-600 hover:underline">345 Reviews</a>
        </div>

        <p class="mt-4 text-3xl font-bold">$1,249.99</p>

        <!-- Quantity + Buttons -->
        <div class="mt-4 flex items-center gap-3">
          <label for="quantity" class="sr-only">Quantity</label>
          <select id="quantity" class="border rounded-lg p-2">
            <option>1</option>
            <option>2</option>
            <option>3</option>
          </select>
          <button class="px-4 py-2 border rounded-lg text-sm">Add to favorites</button>
        </div>

        <button class="mt-4 w-full px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
          Add to cart
        </button>

        <p class="mt-2 text-xs text-gray-600">
          Also available at competitive prices from 
          <a href="#" class="text-blue-600 hover:underline">authorized retailers</a>, 
          with optional Premium delivery for expedited shipping.
        </p>

        <!-- Options -->
        <div class="mt-6">
          <h3 class="mb-2 text-sm font-semibold">Colour</h3>
          <div class="flex gap-2">
            <button class="px-3 py-1 border rounded-lg">Green</button>
            <button class="px-3 py-1 border rounded-lg">Pink</button>
            <button class="px-3 py-1 border rounded-lg">Silver</button>
            <button class="px-3 py-1 border rounded-lg">Blue</button>
          </div>a
        </div>

        <div class="mt-6">
          <h3 class="mb-2 text-sm font-semibold">SSD capacity</h3>
          <div class="flex gap-2">
            <button class="px-3 py-1 border rounded-lg">256GB</button>
            <button class="px-3 py-1 border rounded-lg">512GB</button>
            <button class="px-3 py-1 border rounded-lg">1TB</button>
          </div>
        </div>

        <!-- Pickup -->
        <div class="mt-6">
          <h3 class="mb-2 text-sm font-semibold">Pickup</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <label class="flex items-center gap-2">
                <input type="radio" name="pickup" class="text-blue-600"> 
                Shipping - $19 <span class="text-gray-500">(Arrives Nov 17)</span>
              </label>
            </li>
            <li>
              <label class="flex items-center gap-2">
                <input type="radio" name="pickup" class="text-blue-600"> 
                Pickup from Flowbox - $9 
                <a href="#" class="text-blue-600 hover:underline ml-1">Pick a Flowbox near you</a>
              </label>
            </li>
            <li class="text-gray-400">Pickup from our store - Not available</li>
          </ul>
        </div>

        <!-- Warranty -->
        <div class="mt-6">
          <h3 class="mb-2 text-sm font-semibold">Add extra warranty</h3>
          <ul class="space-y-2 text-sm">
            <li>1 year - $39</li>
            <li>2 years - $69</li>
            <li>3 years - $99</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

</section>
