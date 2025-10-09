<div>

<div class="bg-gray-900 bg-cover bg-center w-full h-screen relative" style = "background-image: url('images/cg-shoestore.png');" >
  <div class="absolute inset-0 bg-black/60"></div>
  
  
  <div class="relative z-10 mx-auto max-w-2xl py-32 sm:py-48 lg:py-56 text-center">
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
      <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75"></div>
    </div>

    <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
      <div class="hidden sm:mb-8 sm:flex sm:justify-center">
        <div class="relative rounded-full px-3 py-1 text-sm/6 text-white ring-1 ring-white/10 hover:ring-white/20">
          Browse our newest products <a href="#" class="font-semibold text-indigo-400"><span aria-hidden="true" class="absolute inset-0"></span>Find more <span aria-hidden="true">&rarr;</span></a>
        </div>
      </div>
      <div class="text-center">
        <h1 class="text-5xl font-semibold tracking-tight text-balance text-white sm:text-7xl">Where Shopping Meets Convenience</h1>
        <p class="mt-8 text-lg font-medium text-pretty text-gray-200 sm:text-xl/8">"Experience shopping that’s fast, seamless, and made for today’s lifestyle. From trending products to everyday essentials, we bring it all to your fingertips."</p>
          
        <div class="mt-10 flex items-center justify-center gap-x-6">
          <!-- <a href="#" class="rounded-md bg-indigo-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">Get started</a> -->
          <!-- <a href="#" class="text-sm/6 font-semibold text-white">Learn more <span aria-hidden="true">→</span></a> -->
        </div>
      </div>
    </div>
    <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
      <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75"></div>
    </div>
  </div>
</div>




<!-- Brand start section -->
<section class="bg-gray-100 py-20">
  <div class="max-w-xl mx-auto">
    <div class="text-center ">
      <div class="relative flex flex-col items-center">
        <h1 class="text-5xl font-bold dark:text-gray-200 "> Browse Popular<span class="text-blue-500"> Brands
          </span> </h1>
        <div class="flex w-40 mt-2 mb-6 overflow-hidden rounded">
          <div class="flex-1 h-2 bg-blue-200">
          </div>
          <div class="flex-1 h-2 bg-blue-400">
          </div>
          <div class="flex-1 h-2 bg-blue-600">
          </div>
        </div>
      </div>
      <p class="mb-12 text-base text-center ">
        <!-- Lorem ipsum, dolor sit amet consectetur adipisicing elit. Delectus magni eius eaque?
        Pariatur
        numquam, odio quod nobis ipsum ex cupiditate? -->
      </p>
    </div>
  </div>
  <div class="justify-center max-w-6xl px-4 py-4 mx-auto lg:py-0">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 md:grid-cols-2">

@foreach ($brands->take(4) as $brand) 
    <a href="#" wire:key="{{ $brand->id }}" class="block group">
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 overflow-hidden transition-all duration-300 group-hover:shadow-xl group-hover:bg-blue-500"> 
            <!-- Image container with white background -->
            <div class="bg-white p-4 group-hover:bg-white transition-all duration-300">
                @if($brand->name == 'Vans') 
                    <img src="{{ asset('images/vanslogo.png') }}" alt="Vans Logo" class="object-contain w-full h-64 transition-transform duration-300 group-hover:scale-110 mx-auto"> 
                @elseif($brand->name == 'Adidas') 
                    <img src="{{ asset('images/adidaslogo.png') }}" alt="Adidas Logo" class="object-contain w-full h-64 transition-transform duration-300 group-hover:scale-110 mx-auto"> 
                @elseif($brand->name == 'Nike') 
                    <img src="{{ asset('images/nikelogo.png') }}" alt="Nike Logo" class="object-contain w-full h-64 transition-transform duration-300 group-hover:scale-110 mx-auto"> 
                @elseif($brand->name == 'Puma') 
                    <img src="{{ asset('images/pumalogo.png') }}" alt="Puma Logo" class="object-contain w-full h-64 transition-transform duration-300 group-hover:scale-110 mx-auto"> 
                @else 
                    <img src="{{ asset('images/default-logo.png') }}" alt="{{ $brand->name }} Logo" class="object-contain w-full h-64 transition-transform duration-300 group-hover:scale-110 mx-auto"> 
                @endif
            </div>
 
            <!-- Text container -->
            <div class="p-5 text-center transition-all duration-300"> 
                <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-300 group-hover:text-white transition-all duration-300"> 
                    {{ $brand->name }} 
                </h3> 
            </div> 
        </div>
    </a>
@endforeach
    </div>
  </div>
</section>
<!-- Brand end section -->


</div>
</div>