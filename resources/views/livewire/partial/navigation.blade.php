<header 
  class="flex z-40 sticky top-0 flex-wrap md:justify-start md:flex-nowrap w-full bg-[#f2f3f4] text-[13px] py-2 md:py-0 shadow-md transition-all duration-300" 
  id="pageContent" 
  style="font-family: 'Inter', sans-serif;" 
  x-data="{ open: false }"
>
  <nav class="max-w-[85rem] w-full mx-auto px-4 md:px-6 lg:px-8" aria-label="Global">
    <div class="relative md:flex md:items-center md:justify-between">
      <div class="flex items-center justify-between">
        <!-- LOGO -->
        <a href="/" aria-label="Brand">
          <img src="{{ asset('images/logopng.png') }}" alt="Brand Logo" width="110" height="auto" class="object-contain" />
        </a>

        <!-- MOBILE TOGGLE -->
        <div class="md:hidden">
          <button 
            @click="open = !open" 
            class="flex justify-center items-center w-9 h-9 rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100 focus:outline-none"
          >
            <svg x-show="!open" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <line x1="3" x2="21" y1="6" y2="6"/>
              <line x1="3" x2="21" y1="12" y2="12"/>
              <line x1="3" x2="21" y1="18" y2="18"/>
            </svg>
            <svg x-show="open" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- NAV LINKS -->
      <div 
        :class="{'block': open, 'hidden': !open}" 
        class="w-full md:block md:w-auto mt-3 md:mt-0 transition-all duration-300"
      >
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-end md:gap-x-3 md:ps-6">

          <!-- HOME -->
          <a wire:navigate 
             href="/" 
             class="group inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-medium text-[13px] {{ request()->is('/') ? 'text-blue-500' : 'text-gray-700' }} hover:bg-blue-50 transition-all duration-200">
            <span>HOME</span>
          </a>

          <!-- PRODUCTS -->
          <a wire:navigate 
             href="/products" 
             class="group inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-medium text-[13px] {{ request()->is('products') ? 'text-blue-500' : 'text-gray-700' }} hover:bg-blue-50 transition-all duration-200">
            <span>PRODUCTS</span>
          </a>

          <!-- CART -->
          <a wire:navigate 
             href="/cart" 
             class="group inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-medium text-[13px] {{ request()->is('cart') ? 'text-blue-500' : 'text-gray-700' }} hover:bg-blue-50 transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
                 class="w-[17px] h-[17px] mt-[1px] group-hover:text-blue-500 transition-colors">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12H4.25l1.264-12h12.974z" />
            </svg>
            <livewire:cart-counter />
          </a>

          @auth
          <div class="relative flex items-center" x-data="{ openNotif: false }">
            <span class="text-sm font-medium text-gray-700 cursor-pointer hover:text-blue-600" @click="openNotif = !openNotif">
              Notifications
            </span>

            <button @click="openNotif = !openNotif" class="relative flex items-center justify-center w-9 h-9 rounded-lg text-gray-700 hover:bg-blue-50 transition-colors" aria-label="Notifications">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-5-5.917V5a1 1 0 0 0-2 0v.083A6 6 0 0 0 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
              </svg>

              @if($unreadCount > 0)
              <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full">
                {{ $unreadCount }}
              </span>
              @endif
            </button>

            <div x-show="openNotif" @click.away="openNotif = false" x-transition 
              class="absolute right-0 top-full mt-1 w-64 bg-white shadow-lg rounded-lg border border-gray-200 z-50">
              <div class="p-2">
                <h4 class="text-sm font-semibold mb-2">Notifications</h4>
                <ul class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                  @forelse($notifications as $notif)
                    <li 
                      wire:click="markAllAsReadAndRedirect('{{ route('my.orders') }}')"
                      class="px-3 py-2 cursor-pointer hover:bg-blue-100 transition {{ $notif->is_read ? 'bg-white' : 'bg-blue-50' }}">
                      <span class="text-[13px] font-medium text-gray-800">
                        {{ $notif->message }}
                      </span>
                      <span class="block text-gray-400 text-[11px]">
                        {{ $notif->created_at->diffForHumans() }}
                      </span>
                    </li>
                  @empty
                    <li class="px-3 py-2 text-gray-400 text-sm">No new notifications</li>
                  @endforelse
                </ul>
                <a href="{{ route('notifications.page') }}" class="block text-center text-blue-500 text-[13px] mt-2 py-1 hover:underline">
                  View All
                </a>
              </div>
            </div>
          </div>
          @endauth

          <!-- ACCOUNT (GUEST) -->
          @guest
          <a wire:navigate 
            href="/login"
            class="group inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-medium text-[13px] text-gray-700 hover:bg-blue-50 transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px] text-gray-700 group-hover:text-blue-500 transition-colors duration-200">
              <path stroke-linecap="round" stroke-linejoin="round" 
                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zM4 20a8 8 0 0 1 16 0z" />
            </svg>
            <span>ACCOUNT</span>
          </a>
          @endguest

          <!-- ACCOUNT (AUTH) -->
          @auth
          <div class="relative" x-data="{ dropdown: false, userName: '{{ auth()->user()->name }}' }" 
               x-init="
                  window.addEventListener('userNameUpdated', e => { 
                    userName = e.detail; 
                  });">
            <button @click="dropdown = !dropdown" class="group inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-medium text-[13px] text-gray-700 hover:bg-blue-50 transition-all duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-[18px] h-[18px] text-gray-700 group-hover:text-blue-500 transition-colors duration-200">
                <path fill-rule="evenodd" 
                  d="M12 2.25a4.5 4.5 0 0 0-4.5 4.5 4.5 4.5 0 0 0 9 0 4.5 4.5 0 0 0-4.5-4.5Zm-7.5 18a7.5 7.5 0 0 1 15 0 .75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75Z" 
                  clip-rule="evenodd" />
              </svg>
              <span x-text="userName"></span>
              <svg class="w-3.5 h-3.5 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M6 9l6 6 6-6"/>
              </svg>
            </button>

            <div 
              x-show="dropdown"
              @click.away="dropdown = false"
              x-transition
              class="absolute right-0 mt-2 w-44 bg-white shadow-md rounded-lg p-2 border border-gray-200 z-50"
            >
              <a href="{{ route('my.orders') }}" class="block px-3 py-1.5 text-[13px] hover:bg-gray-100">My Orders</a>
              <a href="#" class="block px-3 py-1.5 text-[13px] hover:bg-gray-100">My Favorites</a>
              <a href="{{ route('my.account') }}" class="block px-3 py-1.5 text-[13px] hover:bg-gray-100">My Account</a>
              <a href="/logout" class="block px-3 py-1.5 text-[13px] hover:bg-gray-100 text-red-600">Logout</a>
            </div>
          </div>
          @endauth

        </div>
      </div>
    </div>
  </nav>
</header>
