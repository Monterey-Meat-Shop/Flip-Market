<?php


namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecentReportsStats extends BaseWidget
{
    protected ?string $heading = 'Realtime Orders Stats';

    protected function getStats(): array
    {
        $ordersCount = 0;
        $ordersTotal = 0.0;
        $ordersLast7 = 0;

        if (Schema::hasTable('orders')) {
            $table = 'orders';

            // count (real-time)
            $ordersCount = (int) DB::table($table)->count();

            // detect likely amount column
            $amountColumn = null;
            foreach (['total', 'total_amount', 'amount', 'grand_total'] as $c) {
                if (Schema::hasColumn($table, $c)) {
                    $amountColumn = $c;
                    break;
                }
            }

            if ($amountColumn) {
                $ordersTotal = (float) DB::table($table)->sum($amountColumn);
            }

            $ordersLast7 = (int) DB::table($table)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count();
        }

        $formatCurrency = fn($v) => '₱' . number_format((float) $v, 2);

        return [
            Stat::make('orders_product', 'Orders')
                ->value((string) $ordersCount)
                ->description('Total orders')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('orders_total', 'Orders Total')
                ->value($formatCurrency($ordersTotal))
                ->description('Sum of order totals (real-time)')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),

            Stat::make('orders_last7', 'Last 7d')
                ->value((string) $ordersLast7)
                ->description('Orders in last 7 days')
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}