<?php

namespace App\Filament\Resources\OrderResource\Widgets;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending', Order::query()->where('order_status', 'pending')->count()),
            Stat::make('Processing', Order::query()->where('order_status', 'processing')->count()),
            Stat::make('Shipped', Order::query()->where('order_status', 'shipped')->count()),
            Stat::make('Completed', Order::query()->where('order_status', 'completed')->count()),
        ];
    }
}
