<header class="flex z-40 sticky top-0 flex-wrap md:justify-start md:flex-nowrap w-full bg-[#f2f3f4] text-sm py-3 md:py-0 shadow-md transition-all duration-300" id="pageContent" style="font-family: 'Inter', sans-serif;">
  <nav class="max-w-[85rem] w-full mx-auto px-4 md:px-6 lg:px-8" aria-label="Global">
    <div class="relative md:flex md:items-center md:justify-between">
      <div class="flex items-center justify-between">
        <a href="/" aria-label="Brand">
          <img src="{{ asset('images/logopng.png') }}" alt="Brand Logo" width="120" height="auto" />
        </a>

        <div class="md:hidden">
          <button type="button" class="hs-collapse-toggle flex justify-center items-center w-9 h-9 rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100" data-hs-collapse="#navbar-collapse-with-animation" aria-controls="navbar-collapse-with-animation">
            <svg class="hs-collapse-open:hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <line x1="3" x2="21" y1="6" y2="6"/>
              <line x1="3" x2="21" y1="12" y2="12"/>
              <line x1="3" x2="21" y1="18" y2="18"/>
            </svg>
            <svg class="hs-collapse-open:block hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M18 6 6 18"/>
              <path d="m6 6 12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <div id="navbar-collapse-with-animation" class="hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow md:block">
        <div class="overflow-hidden overflow-y-auto max-h-[75vh] [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-slate-700 dark:[&::-webkit-scrollbar-thumb]:bg-slate-500">
          <div class="flex flex-col gap-x-0 mt-5 divide-y divide-dashed divide-gray-200 md:flex-row md:items-center md:justify-end md:gap-x-3 md:mt-0 md:ps-7 md:divide-y-0 md:divide-solid dark:divide-gray-700">

            <!-- HOME -->
            <a wire:navigate 
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg font-normal text-sm {{ request()->is('/') ? 'text-blue-400' : 'text-gray-700' }} dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all duration-300 ease-in-out" 
               href="/" 
               aria-current="page">
              <span class="group-hover:text-blue-400 transition-colors duration-300">
                HOME
              </span>
            </a>

            <!-- PRODUCTS -->
            <a wire:navigate 
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg font-normal text-sm {{ request()->is('products') ? 'text-blue-400' : 'text-gray-700' }} dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all duration-300 ease-in-out" 
               href="/products">
              <span class="group-hover:text-blue-400 transition-colors duration-300">
                PRODUCTS
              </span>
            </a>

            <!-- CART -->
            <a wire:navigate 
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg font-normal text-sm {{ request()->is('cart') ? 'text-blue-400' : 'text-gray-700' }} dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all duration-300 ease-in-out" 
               href="/cart">
              <div class="flex items-center justify-center w-10 h-10 group-hover:scale-110 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 {{ request()->is('cart') ? 'text-blue-400' : 'text-gray-700' }} dark:text-gray-300 group-hover:text-blue-400 transition-colors duration-300">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
              </div>
              <span class="inline-flex items-center gap-2 group-hover:text-blue-400 transition-colors duration-300">
                <livewire:cart-counter />
              </span>
            </a>

            <!-- ACCOUNT -->
            <div class="pt-3 md:pt-0">
              @guest
                <a wire:navigate 
                   class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg font-normal text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all duration-300 ease-in-out" 
                   href="/login">
                   <!-- Account Icon Without Background -->
                  <div class="flex items-center justify-center w-10 h-10 group-hover:scale-110 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-gray-700 dark:text-gray-300 group-hover:text-blue-400 transition-colors duration-300">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                  </div>
                  <!-- Text with hover effect -->
                  <span class="group-hover:text-blue-400 transition-colors duration-300">
                    ACCOUNT
                  </span>
                </a>
              @endguest
            </div>

            @auth
              <div class="hs-dropdown [--strategy:static] md:[--strategy:fixed] md:[--trigger:hover] md:py-4 relative inline-flex">
                <button type="button" class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg font-normal text-sm text-gray-700 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all duration-300 ease-in-out">
                  <div class="flex items-center justify-center w-10 h-10 group-hover:scale-110 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-gray-700 dark:text-gray-300 group-hover:text-blue-400 transition-colors duration-300">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                  </div>
                  <span class="group-hover:text-blue-400 transition-colors duration-300">
                    {{ auth()->user()->name }}
                  </span>
                  <svg class="w-4 h-4 group-hover:text-blue-400 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </button>

                <div class="hs-dropdown-menu transition-[opacity,margin] duration-[0.1ms] md:duration-[150ms] hs-dropdown-open:opacity-100 opacity-0 md:w-48 hidden z-10 bg-white md:shadow-md rounded-lg p-2 before:absolute top-full md:border border-gray-300 before:-top-5 before:start-0 before:w-full before:h-5">
                  <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500" href="{{ route('my.orders') }}">
                    My Orders
                  </a>

                  <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500" href="{{ route('my.account') }}">
                    My Account
                  </a>
                  <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2 focus:ring-gray-500" href="/logout">
                    Logout
                  </a>
                </div>
              </div>
            @endauth
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
