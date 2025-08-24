<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get all orders for the current day and month to ensure consistency
        $todayOrders = Order::whereDate('created_at', today());
        $thisMonthOrders = Order::whereYear('created_at', now()->year())->whereMonth('created_at', now()->month());

        // Get the total number of orders for today.
        $totalOrdersToday = $todayOrders->count();

        // Get the total sales for today.
        $totalSalesToday = $todayOrders->sum('total_amount');

        // Check if there are any orders this month before calculating the average.
        $monthlyAverageSales = $thisMonthOrders->count() > 0 ? $thisMonthOrders->avg('total_amount') : 0;
        
        return [
            Stat::make('Today\'s Completed Orders', Order::where('order_status', 'completed')->whereDate('created_at', today())->count())
                ->description('Orders completed today')
                ->color('success'),
            
            Stat::make('Today\'s Total Sales', '₱' . number_format($totalSalesToday, 2))
                ->description('Total sales today')
                ->color('info'),

            Stat::make('This Month\'s Average Sales', '₱' . number_format($monthlyAverageSales, 2))
                ->description('Average sales for this month')
                ->color('primary'),
        ];
    }
}
