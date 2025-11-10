<footer class="bg-gray-900 text-gray-300 w-full" style="font-family: 'Roboto', sans-serif;">
    <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        {{-- Top area --}}
        <div class="grid gap-8 md:gap-10 lg:gap-16 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-start">
            {{-- Brand / Intro --}}
            <div class="lg:col-span-2 space-y-4">
                <a href="/" aria-label="Flip Market" class="inline-flex items-center gap-2">
                    <img src="{{ asset('images/logopng.png') }}" alt="Flip Market Logo"
                         class="h-10 w-auto invert-logo">
                </a>
                <p class="text-sm text-gray-400 leading-relaxed max-w-md">
                    Flip Market is your go-to online and in-store destination for authentic sneakers and streetwear.
                    Shop trusted brands, secure checkout, and hassle-free service.
                </p>
            </div>

            {{-- Product Links --}}
            <div class="space-y-3">
                <h4 class="text-base font-semibold tracking-wide text-white uppercase">
                    PRODUCT
                </h4>
                <nav class="space-y-2 text-sm">
                    <a href="/products"
                       class="block text-gray-400 hover:text-white transition-colors">
                        All Products
                    </a>
                    <a href="/products"
                       class="block text-gray-400 hover:text-white transition-colors">
                        Featured Products
                    </a>
                </nav>
            </div>

            {{-- About Links --}}
            <div class="space-y-3">
                <h4 class="text-base font-semibold tracking-wide text-white uppercase">
                    ABOUT
                </h4>
                <nav class="space-y-2 text-sm">
                    <a href="#"
                       class="block text-gray-400 hover:text-white transition-colors">
                        About Us
                    </a>
                    <a href="#"
                       class="block text-gray-400 hover:text-white transition-colors">
                        Blog
                    </a>
                    <a href="#"
                       class="block text-gray-400 hover:text-white transition-colors">
                        Customers
                    </a>
                </nav>
            </div>
        </div>

        {{-- Divider (now solid white) --}}
        <div class="mt-8 border-t border-white"></div>

        {{-- Bottom bar --}}
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs sm:text-sm text-gray-400">
                © 2025 Flip Market. All rights reserved.
            </p>

            <div class="flex flex-col items-start sm:items-end gap-1.5">
                <div class="flex items-center gap-3">
                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/profile.php?id=61555031943368"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-600 text-gray-200 hover:text-blue-500 hover:border-blue-500 hover:bg-blue-500/10 transition-colors"
                       aria-label="Visit Flip Market on Facebook">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path
                                d="M22 12.07C22 6.51 17.52 2 12 2S2 6.51 2 12.07C2 17.1 5.66 21.29 10.44 22v-6.99H7.9v-2.94h2.54v-2.2c0-2.5 1.5-3.89 3.8-3.89 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.86h2.78l-.44 2.94h-2.34V22C18.34 21.29 22 17.1 22 12.07z" />
                        </svg>
                    </a>

                    {{-- Google Maps --}}
                    <a href="https://www.google.com/maps/place/FLIP+MARKET+PH/@14.6982301,121.107318,17z/data=!3m1!4b1!4m6!3m5!1s0x3397bbd1c8ab1d7f:0xcdbd9ddc7510d9c6!8m2!3d14.6982301!4d121.1098929!16s%2Fg%2F11w7gkwhpk?authuser=0&entry=ttu"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-600 text-gray-200 hover:text-green-400 hover:border-green-400 hover:bg-green-500/10 transition-colors"
                       aria-label="View Flip Market PH on Google Maps">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 21s-6-4.35-6-10a6 6 0 0 1 12 0c0 5.65-6 10-6 10z"/>
                            <circle cx="12" cy="11" r="2.6" />
                        </svg>
                    </a>
                </div>
                <p class="text-[10px] sm:text-xs text-white">
                    Visit our physical store via Google Maps.
                </p>
            </div>
        </div>
    </div>

    <style>
        .invert-logo {
            filter: brightness(0) invert(1);
        }
    </style>
</footer>
