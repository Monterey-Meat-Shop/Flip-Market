<div>
    <flux:main container>
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-800 dark:to-purple-800 rounded-xl p-8 mb-8 text-white">
            <flux:heading size="2xl" level="1" class="text-white mb-4">Welcome to FlipMarket</flux:heading>
            <flux:text class="text-xl mb-6 text-blue-100">Discover amazing deals on everything you love</flux:text>
            <div class="flex gap-4">
                <flux:button variant="filled" class="bg-white text-blue-600 hover:bg-blue-50">Shop Now</flux:button>
                <flux:button variant="ghost" class="text-white border-white hover:bg-white/10">View Deals</flux:button>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" level="2" class="mb-4">Special Offers</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    class="bg-gradient-to-r from-red-500 to-pink-500 text-white p-6 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-white mb-2">Flash Sale</h3>
                    <p class="text-red-100 mb-4">Up to 70% off on selected items</p>
                    <flux:button variant="ghost" class="text-white border-white hover:bg-white/10">Shop Flash Sale
                    </flux:button>
                </div>

                <div
                    class="bg-gradient-to-r from-green-500 to-teal-500 text-white p-6 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-white mb-2">Free Shipping</h3>
                    <p class="text-green-100 mb-4">On orders over $50</p>
                    <flux:button variant="ghost" class="text-white border-white hover:bg-white/10">Learn More
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:input placeholder="Search for products, brands, and more..." class="w-full" icon="magnifying-glass"
                size="lg" />
        </div>

        <div class="mb-8 w-full">
            <flux:heading size="lg" level="2" class="mb-4">Shop by Category</flux:heading>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <flux:button variant="outline" class="w-full py-6 justify-center text-lg font-semibold">
                    Shoes
                </flux:button>

                <flux:button variant="outline" class="w-full py-6 justify-center text-lg font-semibold">
                    Shirt
                </flux:button>

                <flux:button variant="outline" class="w-full py-6 justify-center text-lg font-semibold">
                    Hoodie
                </flux:button>

                <flux:button variant="outline" class="w-full py-6 justify-center text-lg font-semibold">
                    Shorts
                </flux:button>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" level="2" class="mb-4">Products</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <x-product-card :id="$product['productID']" :name="$product['name']" :description="$product['description']" :price="$product['price']"
                        :image="$product['image_url'][0] ?? ''" />
                @endforeach


                @if ($products->isEmpty())
                    <p>No products found.</p>
                @endif
            </div>
        </div>

        {{ $products->links() }}


    </flux:main>
</div>
