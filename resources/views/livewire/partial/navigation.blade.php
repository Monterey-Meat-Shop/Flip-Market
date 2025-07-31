<div>
    <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo.png" name="FlipMarket" class="max-lg:hidden dark:hidden" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/dark-mode-logo.png" name="FlipMarket" class="max-lg:hidden! hidden dark:flex" />
        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="home" href="#" current>Home</flux:navbar.item>
            <flux:navbar.item icon="fire" href="#">Trending</flux:navbar.item>
            <flux:navbar.item icon="tag" href="#">Deals</flux:navbar.item>
        </flux:navbar>
        <flux:spacer />

        <flux:navbar class="me-4">
            <flux:navbar.item class="max-lg:hidden" icon="heart" href="#" label="Wishlist" />
            <flux:navbar.item icon="shopping-cart" badge="3" href="#" label="Cart" />
        </flux:navbar>
        
        @if($isAuthenticated)
            <flux:dropdown position="top" align="start">
                <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />
                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                        <flux:menu.radio>Truly Delta</flux:menu.radio>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.item icon="user">My Account</flux:menu.item>
                    <flux:menu.item icon="shopping-bag">My Orders</flux:menu.item>
                    <flux:menu.item icon="heart">Wishlist</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        @else
            <div class="gap-2">
                <flux:button variant="ghost">Login</flux:button>
                <flux:button variant="primary">Signup</flux:button>
            </div>
        @endif

    </flux:header>
    
    <flux:sidebar stashable sticky class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo.png" name="FlipMarket" class="px-2 dark:hidden" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/dark-mode-logo.png" name="FlipMarket" class="px-2 hidden dark:flex" />
        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" href="#" current>Home</flux:navlist.item>
            <flux:navlist.item icon="fire" href="#">Trending</flux:navlist.item>
            <flux:navlist.item icon="tag" href="#">Deals</flux:navlist.item>
        </flux:navlist>
        <flux:spacer />
        <flux:navlist variant="outline">
            <flux:navlist.item icon="user" href="#">My Account</flux:navlist.item>
            <flux:navlist.item icon="shopping-bag" href="#">My Orders</flux:navlist.item>
            <flux:navlist.item icon="heart" href="#">Wishlist</flux:navlist.item>
            <flux:navlist.item icon="question-mark-circle" href="#">Help</flux:navlist.item>
        </flux:navlist>
    </flux:sidebar>

</div>
