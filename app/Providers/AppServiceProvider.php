<?php

namespace App\Providers;
use EightyNine\Reports\ReportsPlugin;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Observers\ProductObserver;
use App\Models\OrderItem;
use App\Observers\OrderItemObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        OrderItem::observe(OrderItemObserver::class);
    }

    public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugins([
            ReportsPlugin::make()
        ]);
}
}
