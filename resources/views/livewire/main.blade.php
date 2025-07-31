<div>
    <flux:main container>
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-800 dark:to-purple-800 rounded-xl p-8 mb-8 text-white">
            <flux:heading size="2xl" level="1" class="text-white mb-4">Welcome to FlipMarket</flux:heading>
            <flux:text class="text-xl mb-6 text-blue-100">Discover amazing deals on everything you love</flux:text>
            <div class="flex gap-4">
                <flux:button variant="filled" class="bg-white text-blue-600 hover:bg-blue-50">Shop Now</flux:button>
                <flux:button variant="ghost" class="text-white border-white hover:bg-white/10">View Deals</flux:button>
            </div>
        </div>

        <div class="mb-8">
            <flux:input 
                placeholder="Search for products, brands, and more..." 
                class="w-full"
                icon="magnifying-glass"
                size="lg"
            />
        </div>

        <div class="mb-8">
            <flux:heading size="lg" level="2" class="mb-4">Shop by Category</flux:heading>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 text-center p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="w-12 h-12 mx-auto mb-2 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <flux:icon name="device-phone-mobile" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Electronics</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 text-center p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="w-12 h-12 mx-auto mb-2 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center">
                        <flux:icon name="sparkles" class="w-6 h-6 text-pink-600 dark:text-pink-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Fashion</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 text-center p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="w-12 h-12 mx-auto mb-2 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <flux:icon name="home" class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Home</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 text-center p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="w-12 h-12 mx-auto mb-2 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                        <flux:icon name="trophy" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Sports</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 text-center p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="w-12 h-12 mx-auto mb-2 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                        <flux:icon name="book-open" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Books</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 text-center p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="w-12 h-12 mx-auto mb-2 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                        <flux:icon name="ellipsis-horizontal" class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">More</p>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" level="2" class="mb-4">Featured Products</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 group cursor-pointer hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg mb-4 flex items-center justify-center">
                        <flux:icon name="device-phone-mobile" class="w-16 h-16 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">iPhone 15 Pro</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Latest Apple smartphone with titanium design</p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-lg text-zinc-900 dark:text-zinc-100">$999</span>
                        <flux:button size="sm" variant="filled">Add to Cart</flux:button>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 group cursor-pointer hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg mb-4 flex items-center justify-center">
                        <flux:icon name="computer-desktop" class="w-16 h-16 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">MacBook Air M3</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Powerful laptop for work and creativity</p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-lg text-zinc-900 dark:text-zinc-100">$1,299</span>
                        <flux:button size="sm" variant="filled">Add to Cart</flux:button>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 group cursor-pointer hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg mb-4 flex items-center justify-center">
                        <flux:icon name="speaker-wave" class="w-16 h-16 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">AirPods Pro</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Wireless earbuds with noise cancellation</p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-lg text-zinc-900 dark:text-zinc-100">$249</span>
                        <flux:button size="sm" variant="filled">Add to Cart</flux:button>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 group cursor-pointer hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg mb-4 flex items-center justify-center">
                        <flux:icon name="tv" class="w-16 h-16 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Smart TV 55"</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">4K Ultra HD Smart Television</p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-lg text-zinc-900 dark:text-zinc-100">$699</span>
                        <flux:button size="sm" variant="filled">Add to Cart</flux:button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" level="2" class="mb-4">Special Offers</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-r from-red-500 to-pink-500 text-white p-6 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-white mb-2">Flash Sale</h3>
                    <p class="text-red-100 mb-4">Up to 70% off on selected items</p>
                    <flux:button variant="ghost" class="text-white border-white hover:bg-white/10">Shop Flash Sale</flux:button>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-teal-500 text-white p-6 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-white mb-2">Free Shipping</h3>
                    <p class="text-green-100 mb-4">On orders over $50</p>
                    <flux:button variant="ghost" class="text-white border-white hover:bg-white/10">Learn More</flux:button>
                </div>
            </div>
        </div>

       
    </flux:main>
</div>
