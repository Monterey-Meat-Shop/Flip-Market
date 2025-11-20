<div>
<footer class="bg-gray-900 text-gray-300 w-full" style="font-family: 'Roboto', sans-serif;">
    <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        {{-- Top area --}}
        <div class="grid gap-8 md:gap-10 lg:gap-16 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-start">
            
            {{-- Brand / Intro --}}
            <div class="lg:col-span-2 space-y-4">
                <a href="/" aria-label="Flip Market" class="inline-flex items-center gap-2">
                    <img src="{{ asset('images/logopng.png') }}" alt="Flip Market Logo"
                    class="h-10 w-auto invert brightness-0 contrast-200">
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
                    <button onclick="openAboutModal()"
                            class="block text-gray-400 hover:text-white transition-colors text-left">
                        About Us
                    </button>

                    <button onclick="openBlogModal()"
                            class="block text-gray-400 hover:text-white transition-colors text-left">
                        Blog
                    </button>

                    <button onclick="openPoliciesModal()"
                            class="block text-gray-400 hover:text-white transition-colors text-left">
                        Terms & Conditions / Privacy Policy
                    </button>
                </nav>
            </div>
        </div>

        {{-- Divider --}}
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
                       target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-600 text-gray-200 hover:text-blue-500 hover:border-blue-500 hover:bg-blue-500/10">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12.07C22 6.51 17.52 2 12 2S2 6.51 2 12.07C2 17.1 5.66 21.29 10.44 22v-6.99H7.9v-2.94h2.54v-2.2c0-2.5 1.5-3.89 3.8-3.89 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.86h2.78l-.44 2.94h-2.34V22C18.34 21.29 22 17.1 22 12.07z"/>
                        </svg>
                    </a>

                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/flipmarket.ph"
                       target="_blank"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-600 text-gray-200 hover:text-pink-500 hover:border-pink-500 hover:bg-pink-500/10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="2" y="2" width="20" height="20" rx="5" stroke-width="2"/>
                            <circle cx="12" cy="12" r="4" stroke-width="2"/>
                            <circle cx="17" cy="7" r="1.2" fill="currentColor"/>
                        </svg>
                    </a>

                    {{-- GOOGLE MAPS --}}
                    <a href="https://www.google.com/maps/place/FLIP+MARKET+PH/@14.6982301,121.107318,842m/data=!3m2!1e3!4b1!4m6!3m5!1s0x3397bbd1c8ab1d7f:0xcdbd9ddc7510d9c6!8m2!3d14.6982301!4d121.1098929!16s%2Fg%2F11w7gkwhpk?entry=ttu"
                       target="_blank"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-600 text-gray-200 hover:text-green-400 hover:border-green-400 hover:bg-green-500/10">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
                        </svg>
                    </a>

                </div>
                <p class="text-[10px] sm:text-xs text-white">
                    Visit our physical store via Google Maps.
                </p>
            </div>
        </div>
    </div>
</footer>


<!-- ABOUT MODAL -->
<div id="aboutModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">

    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6 animate-fadeIn">

        <!-- BOXED CLOSE X -->
        <button onclick="closeAboutModal()"
                class="absolute top-3 right-3 p-2 rounded-full text-gray-700 hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="3" />
                <line x1="8" y1="8" x2="16" y2="16" stroke-linecap="round"/>
                <line x1="16" y1="8" x2="8" y2="16" stroke-linecap="round"/>
            </svg>
        </button>

        <h2 class="text-2xl font-bold text-gray-900 text-center mb-1">About Flip Market</h2>
        <p class="text-sm text-gray-500 text-center mb-6">
            Authentic sneakers & streetwear — trusted by the community.
        </p>

        <p class="text-sm text-gray-700 leading-relaxed mb-4">
            Flip Market began as a small buy-and-sell community driven by passion for sneakers &
            streetwear. Today, we serve shoppers online and in-store — offering authentic pairs,
            secure checkout, and reliable service.
        </p>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Our Mission</h3>
            <p class="text-sm text-gray-600">
                To deliver authentic, high-quality sneakers & apparel paired with excellent customer experience.
            </p>
        </div>

        <!-- STORE INFO CARD -->
        <div class="bg-gray-900 text-white rounded-xl p-4 mb-5">
            <h3 class="text-lg font-semibold mb-2">Visit Our Store</h3>

            <p class="text-sm opacity-90 leading-snug mb-3">
                005 Bonifacio, Bagong Silangan,<br>
                Quezon City, Metro Manila 1119
            </p>

            <a href="https://www.google.com/maps/place/FLIP+MARKET+PH/@14.6982301,121.107318,842m/data=!3m2!1e3!4b1!4m6!3m5!1s0x3397bbd1c8ab1d7f:0xcdbd9ddc7510d9c6!8m2!3d14.6982301!4d121.1098929!16s%2Fg%2F11w7gkwhpk?entry=ttu"
               target="_blank"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
                </svg>
                View on Google Maps
            </a>
        </div>

        <a href="https://www.instagram.com/flipmarket.ph"
           target="_blank"
           class="px-5 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold rounded-lg flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor">
                <path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2c1.65 0 3 1.35 3 3v10c0 1.65-1.35 3-3 3H7c-1.65 0-3-1.35-3-3V7c0-1.65 1.35-3 3-3h10zm-5 3a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6zm4.8-.9a1.1 1.1 0 11-2.2 0 1.1 1.1 0 012.2 0z"/>
            </svg>
            IG
        </a>
    </div>
</div>


<!-- BLOG MODAL -->
<div id="blogModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">

    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6 animate-fadeIn">

        <!-- BOXED CLOSE X -->
        <button onclick="closeBlogModal()"
                class="absolute top-3 right-3 p-2 rounded-full text-gray-700 hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="3" />
                <line x1="8" y1="8" x2="16" y2="16" stroke-linecap="round"/>
                <line x1="16" y1="8" x2="8" y2="16" stroke-linecap="round"/>
            </svg>
        </button>

        <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Blog & Updates</h2>
        <p class="text-sm text-gray-500 text-center mb-6">
            Latest news, sneaker drops, and Flip Market updates.
        </p>

        <p class="text-sm text-gray-700 leading-relaxed mb-4">
            Stay updated with sneaker releases, product features, and behind-the-scenes stories.
            Our blog highlights the culture, trends, and community that make Flip Market unique.
        </p>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Sneaker Culture</h3>
            <p class="text-sm text-gray-600">
                From classics to hype drops — discover insights and stories behind your favorite pairs.
            </p>
        </div>

    </div>
</div>


<!-- TERMS & PRIVACY MODAL -->
<div id="policiesModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">

    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6 animate-fadeIn">

        <!-- BOXED CLOSE X -->
        <button onclick="closePoliciesModal()"
                class="absolute top-3 right-3 p-2 rounded-full text-gray-700 hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="3" />
                <line x1="8" y1="8" x2="16" y2="16" stroke-linecap="round"/>
                <line x1="16" y1="4" x2="8" y2="20" stroke-linecap="round"/>
            </svg>
        </button>

        <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Terms & Conditions</h2>
        <p class="text-sm text-gray-600 leading-relaxed mb-4">
            By accessing and using Flip Market’s website and services, you agree to abide by our
            guidelines on purchases, returns, authenticity, and customer responsibilities.
        </p>

        <div class="bg-gray-100 border border-gray-200 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-800">Privacy Policy</h3>
            <p class="text-sm text-gray-600 mt-1">
                We value your privacy. All personal information collected during checkout or
                registration is kept secure and used solely for order processing and customer support.
            </p>
        </div>

    </div>
</div>


<!-- Animation -->
<style>
    .animate-fadeIn {
        animation: fadeIn .25s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    function openAboutModal() { document.getElementById('aboutModal').classList.remove('hidden'); }
    function closeAboutModal() { document.getElementById('aboutModal').classList.add('hidden'); }

    function openBlogModal() { document.getElementById('blogModal').classList.remove('hidden'); }
    function closeBlogModal() { document.getElementById('blogModal').classList.add('hidden'); }

    function openPoliciesModal() { document.getElementById('policiesModal').classList.remove('hidden'); }
    function closePoliciesModal() { document.getElementById('policiesModal').classList.add('hidden'); }
</script>

</div>
