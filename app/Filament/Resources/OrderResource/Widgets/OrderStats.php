<?php

namespace App\Filament\Resources\OrderResource\Widgets;
use App\Models\Order;
use App\Models\Shipping;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending', fn () => Order::where('order_status', 'pending')->count())
                ->icon('heroicon-o-clock') 
                ->color('warning')
                ->description('Orders waiting to be processed'),

            Stat::make('Processing', Order::query()
                ->where('order_status', 'processing')
                ->count())
                ->icon('heroicon-o-forward')
                ->description('Orders currently being processed')
                ->color('info'),

            Stat::make('In Transit', Shipping::query()->where('shipping_status', 'in_transit')->count())
                ->description('Orders currently in delivery')
                ->icon('heroicon-o-truck')
                ->color('info'),

            Stat::make('Delivered', Shipping::query()->where('shipping_status', 'delivered')->count())
                ->description('Orders successfully delivered')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
