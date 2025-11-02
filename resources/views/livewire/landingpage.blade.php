<div>

<!-- Hero Section with Background -->
<div class="bg-gray-900 bg-cover bg-center w-full h-screen relative" style="background-image: url('images/cg-shoestore.png');">
  <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/60 to-black/70"></div>
  
  <!-- Decorative Gradient Blobs -->
  <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
    <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
  </div>

  <div class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:py-24">
    <!-- Announcement Badge -->
    <div class="flex justify-center mb-8 animate-fade-in">
      <div class="relative rounded-full px-4 py-1.5 text-sm leading-6 text-white ring-1 ring-white/10 hover:ring-white/20 transition-all duration-300 backdrop-blur-sm bg-white/5">
        Browse our newest products 
        <a href="#" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
          <span aria-hidden="true" class="absolute inset-0"></span>
          Find more <span aria-hidden="true">&rarr;</span>
        </a>
      </div>
    </div>

    <!-- Main Heading -->
    <div class="text-center mb-12 animate-fade-in-up">
      <h1 class="text-5xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl mb-6 drop-shadow-2xl">
        Where Shopping Meets <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-pink-400">Convenience</span>
      </h1>
      <p class="text-lg md:text-xl text-gray-200 max-w-3xl mx-auto leading-relaxed">
        Experience shopping that's fast, seamless, and made for today's lifestyle. From trending products to everyday essentials, we bring it all to your fingertips.
      </p>
    </div>

    <!-- Slideshow Section -->
    <div x-data="{ 
      currentSlide: 0,
      slides: [
        { title: 'Summer Collection 2025', description: 'Discover the latest trends in footwear', color: 'from-blue-500 to-purple-600' },
        { title: 'Athletic Performance', description: 'Gear up for your best performance yet', color: 'from-green-500 to-teal-600' },
        { title: 'Casual Comfort', description: 'Style meets comfort in every step', color: 'from-orange-500 to-red-600' },
        { title: 'Limited Edition', description: 'Exclusive designs you won\'t find anywhere else', color: 'from-pink-500 to-rose-600' }
      ],
      autoplay: null,
      init() {
        this.autoplay = setInterval(() => {
          this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        }, 4000);
      }
    }" class="max-w-3xl mx-auto">
      
      <!-- Slideshow Container -->
      <div class="relative overflow-hidden rounded-lg">
        <!-- Slides -->
        <div class="relative h-48 sm:h-56 md:h-64">
          <template x-for="(slide, index) in slides" :key="index">
            <div 
              x-show="currentSlide === index"
              x-transition:enter="transition ease-out duration-700"
              x-transition:enter-start="opacity-0 translate-x-full"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition ease-in duration-700"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 -translate-x-full"
              class="absolute inset-0 flex items-center justify-center px-8 sm:px-12 py-6"
            >
              <!-- Decorative Gradient Element in Center -->
              <div :class="'absolute inset-0 flex items-center justify-center'">
                <div :class="'w-64 md:w-80 h-64 md:h-80 rounded-full bg-gradient-to-br ' + slide.color + ' opacity-20 blur-3xl'"></div>
              </div>
              
              <!-- Text Content Centered -->
              <div class="relative z-10 text-center max-w-lg">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2 drop-shadow-2xl" x-text="slide.title"></h2>
                <p class="text-sm sm:text-base text-white/90 drop-shadow-lg mb-4" x-text="slide.description"></p>
                <a href="{{ route('products') }}" :class="'inline-block px-5 py-2 bg-gradient-to-r ' + slide.color + ' text-white font-semibold rounded-full hover:shadow-lg transform hover:scale-105 transition-all duration-300 shadow-md text-sm'">
                  Shop Now
                </a>
              </div>
            </div>
          </template>
        </div>

        <!-- Navigation Arrows -->
        <button 
          @click="currentSlide = currentSlide === 0 ? slides.length - 1 : currentSlide - 1"
          class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white p-1.5 rounded-full transition-all duration-300 hover:scale-105"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <button 
          @click="currentSlide = (currentSlide + 1) % slides.length"
          class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white p-1.5 rounded-full transition-all duration-300 hover:scale-105"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>

        <!-- Dots Indicator -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
          <template x-for="(slide, index) in slides" :key="index">
            <button 
              @click="currentSlide = index"
              :class="currentSlide === index ? 'bg-white/90 w-5' : 'bg-white/30 w-1.5'"
              class="h-1.5 rounded-full transition-all duration-300 hover:bg-white/60"
            ></button>
          </template>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Decorative Blob -->
  <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
    <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"></div>
  </div>
</div>

<!-- section for Category shoes -->
<section class="bg-gradient-to-b from-gray-50 to-white py-20">
  <div class="max-w-screen-xl mx-auto px-4">
    <div class="text-center">
      <div class="relative flex flex-col items-center mb-12 animate-fade-in-up">
        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900">
          Popular Shoes By
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
            Category
          </span>
        </h2>
      </div>

      <div data-hs-carousel='{
          "loadingClasses": "opacity-0",
          "dotsItemClasses": "hs-carousel-active:bg-blue-700 hs-carousel-active:border-blue-700 size-3 border border-gray-400 rounded-full cursor-pointer dark:border-neutral-600 dark:hs-carousel-active:bg-blue-500 dark:hs-carousel-active:border-blue-500",
          "slidesQty": { "xs": 1, "lg": 3 }
        }'class="relative">

        <div class="hs-carousel w-full overflow-hidden bg-white rounded-lg dark:bg-neutral-900">
          <div class="relative min-h-[350px] -mx-1"> 
            <div class="hs-carousel-body absolute top-0 bottom-0 start-0 flex flex-nowrap transition-transform duration-700">

              @foreach ($products as $product)
              <div class="hs-carousel-slide px-1">
                <div class="flex justify-center h-full p-4">
                  <article class="w-full max-w-sm h-[280px] flex flex-col bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200  shadow-sm transition-transform duration-300 hover:shadow-lg hover:scale-[1.02]">
                    
                    <a href="{{ route('product.detail', $product->productID) }}" class="block flex-grow flex flex-col">
                      <div class="relative overflow-hidden rounded-t-lg h-50 flex-shrink-0">
                        <img 
                          src="{{ $product->image_path ? asset('storage/' . $product->image_path) : 'https://via.placeholder.com/300' }}" 
                          alt="{{ $product->name }}" 
                          class="w-full h-full object-cover"
                        />

                        <span class="absolute top-2 left-2 {{ $product->badge_color }} bg-opacity-80 text-white text-xs px-2 py-1 rounded">
                                  {{ $product->brand_name ?? 'No Brand' }}
                        </span>

                

                     
                      </div>

                      <div class="p-4 flex flex-col justify-between flex-grow">
                        <div>

                            <h2 class="text-slate-700 font-semibold text-lg mb-1 line-clamp-1">
                                  {{ $product->category->name ?? 'No Brand' }}
                            </h2>
                            
                        </div>
                      </div>
                    </a>
                    
                  </article>
                </div>
              </div>
              @endforeach

            </div>
          </div>
        </div>

 <!-- Navigation Buttons -->
<button 
  type="button" 
  class="hs-carousel-prev absolute inset-y-0 -start-10 inline-flex justify-center items-center w-11.5 h-80 text-gray-800 hover:bg-gray-800/10 focus:outline-hidden rounded-s-lg"
>
  <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="m15 18-6-6 6-6"/>
  </svg>
  <span class="sr-only">Previous</span>
</button>

<button 
  type="button" 
  class="hs-carousel-next absolute inset-y-0 -end-10 inline-flex justify-center items-center w-11.5 h-80 text-gray-800 hover:bg-gray-800/10 focus:outline-hidden rounded-e-lg"
>
  <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="m9 18 6-6-6-6"/>
  </svg>
  <span class="sr-only">Next</span>
</button>


      <div class="hs-carousel-pagination flex justify-center gap-x-2 mt-2 relative"></div>
      </div>
    </div>
  </div>
</section>


<!-- Brand Images Section -->
<section class="bg-gradient-to-b from-gray-50 to-white py-20">
  <div class="max-w-screen-xl mx-auto px-4">
    <div class="text-center">
      <div class="relative flex flex-col items-center mb-12 animate-fade-in-up">
        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900">
          Browse Popular <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Brands</span>
        </h2>
        
        <!-- Decorative Bar with Animation -->
        <div class="flex w-40 mt-4 mb-6 overflow-hidden rounded-full mx-auto">
          <div class="flex-1 h-2 bg-blue-300 transition-all duration-300 hover:bg-blue-400 animate-bar-slide" style="animation-delay: 0s;"></div>
          <div class="flex-1 h-2 bg-blue-500 transition-all duration-300 hover:bg-blue-600 animate-bar-slide" style="animation-delay: 0.15s;"></div>
          <div class="flex-1 h-2 bg-blue-700 transition-all duration-300 hover:bg-blue-800 animate-bar-slide" style="animation-delay: 0.3s;"></div>
        </div>

        <!-- <p class="text-lg md:text-xl text-gray-600 max-w-3xl">
          Explore a wide variety of top brands to find the perfect match for your needs. Discover quality and innovation in every product.
        </p> -->
      </div>
      

      
      <!-- Brand Cards with Horizontal Scroll - Centered -->
      <div class="flex justify-center">
        <div class="relative inline-block">
          <div class="flex gap-4 pb-6 animate-slide-in">
            @foreach($brands as $brand)
              <div class="flex-shrink-0 group animate-fade-in-scale" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                <div class="flex justify-center items-center p-4 bg-white/40 backdrop-blur-sm shadow-sm rounded-lg hover:shadow-md hover:bg-white/60 transform transition-all duration-300 ease-in-out hover:-translate-y-1 w-28 sm:w-32 md:w-36 h-20 border border-gray-200/50 {{ $brand->name == 'PUMA' || $brand->name == 'VANS' ? 'bg-yellow-100/40 border-yellow-300/50' : '' }}">
                  <span class="text-sm sm:text-base font-semibold text-gray-700 group-hover:text-blue-600 transition-colors duration-300">{{ $brand->name }}</span>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      
      <!-- Auto-Sliding Logo Section -->
      <div class="mt-16 overflow-hidden">
        <div class="relative">
          <!-- Gradient Overlays -->
          <div class="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
          <div class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
          
          <!-- Sliding Logos Container -->
          <div class="flex animate-scroll-logos">
            <!-- First Set -->
            <div class="flex gap-16 px-8 items-center min-w-max">
              <img src="{{ asset('images/nikelogo.png') }}" alt="Nike" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/adidaslogo.png') }}" alt="Adidas" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/pumalogo.png') }}" alt="Puma" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/vanslogo.png') }}" alt="Vans" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/converselogo.png') }}" alt="Converse" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/nblogo.png') }}" alt="Converse" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">

            </div>
            <!-- Duplicate Set for Seamless Loop -->
            <div class="flex gap-16 px-8 items-center min-w-max">
              <img src="{{ asset('images/nikelogo.png') }}" alt="Nike" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/adidaslogo.png') }}" alt="Adidas" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/pumalogo.png') }}" alt="Puma" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/vanslogo.png') }}" alt="Vans" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/converselogo.png') }}" alt="Converse" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/nblogo.png') }}" alt="Converse" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">

            </div>
            <!-- Third Set for Extra Smoothness -->
            <div class="flex gap-16 px-8 items-center min-w-max">
              <img src="{{ asset('images/nikelogo.png') }}" alt="Nike" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/adidaslogo.png') }}" alt="Adidas" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/pumalogo.png') }}" alt="Puma" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/vanslogo.png') }}" alt="Vans" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/converselogo.png') }}" alt="Converse" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
              <img src="{{ asset('images/nblogo.png') }}" alt="Converse" class="h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300 opacity-60 hover:opacity-100">
            
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  @keyframes fade-in-up {
    from { 
      opacity: 0; 
      transform: translateY(20px);
    }
    to { 
      opacity: 1; 
      transform: translateY(0);
    }
  }
  
  @keyframes slide-in {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @keyframes fade-in-scale {
    from {
      opacity: 0;
      transform: scale(0.8);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  
  @keyframes bar-slide {
    0% {
      transform: scaleX(0);
      transform-origin: left;
    }
    100% {
      transform: scaleX(1);
      transform-origin: left;
    }
  }
  
  @keyframes scroll-logos {
    0% {
      transform: translateX(0);
    }
    100% {
      transform: translateX(-33.333%);
    }
  }
  
  .animate-fade-in {
    animation: fade-in 1s ease-out;
  }
  
  .animate-fade-in-up {
    animation: fade-in-up 1s ease-out;
  }
  
  .animate-slide-in {
    animation: slide-in 0.8s ease-out;
  }
  
  .animate-fade-in-scale {
    animation: fade-in-scale 0.6s ease-out forwards;
    opacity: 0;
  }
  
  .animate-bar-slide {
    animation: bar-slide 0.8s ease-out forwards;
  }
  
  .animate-scroll-logos {
    animation: scroll-logos 20s linear infinite;
  }
  
  .animate-scroll-logos:hover {
    animation-play-state: paused;
  }
  
  .scrollbar-thin::-webkit-scrollbar {
    height: 6px;
  }
  
  .scrollbar-thumb-blue-500::-webkit-scrollbar-thumb {
    background-color: #3b82f6;
    border-radius: 3px;
  }
  
  .scrollbar-track-gray-200::-webkit-scrollbar-track {
    background-color: #e5e7eb;
    border-radius: 3px;
  }
  
  .scrollbar-hidden::-webkit-scrollbar {
    display: none;
  }
</style>

</div>

<script>
document.addEventListener('livewire:load', () => {
    // initialize carousel when page loads
    window.HSCarousel?.autoInit();

    // re-initialize after every Livewire DOM update
    Livewire.hook('message.processed', () => {
        window.HSCarousel?.autoInit();
    });
});

// for Livewire v3 SPA navigation (if you go to another page and come back)
document.addEventListener('livewire:navigated', () => {
    window.HSCarousel?.autoInit();
});
</script>
