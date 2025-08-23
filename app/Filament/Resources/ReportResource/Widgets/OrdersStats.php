<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OrdersStats extends BaseWidget
{
   protected ?string $heading = 'Realtime Orders Stats';

    protected function getStats(): array
    {
        $ttl = 60;
        $ordersCount = 0;
        $ordersTotal = 0.0;
        $ordersLast7 = 0;

        if (! class_exists(Order::class)) {
            return $this->fallbackStats();
        }

        $table = (new Order())->getTable();
        if (! Schema::hasTable($table)) {
            return $this->fallbackStats();
        }

        // count
        $ordersCount = (int) Cache::remember("orders_stats:count", $ttl, fn () => DB::table($table)->count());

        // total (detect common amount columns)
        $amountColumn = Cache::remember("orders_stats:amount_column", $ttl, function () use ($table) {
            foreach (['total', 'total_amount', 'amount', 'grand_total'] as $c) {
                if (Schema::hasColumn($table, $c)) {
                    return $c;
                }
            }
            return null;
        });

        if ($amountColumn) {
            $ordersTotal = (float) Cache::remember("orders_stats:total", $ttl, fn () => DB::table($table)->sum($amountColumn));
        }

        // last 7 days (count)
        $ordersLast7 = (int) Cache::remember("orders_stats:last7_count", $ttl, fn () => DB::table($table)->whereDate('created_at', '>=', now()->subDays(7))->count());

        $formatCurrency = fn($v) => '₱' . number_format((float) $v, 2);

        return [
            Stat::make('orders_product', 'Orders')
                ->value((string) $ordersCount)
                ->description('Total orders')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('orders_total', 'Orders Total')
                ->value($formatCurrency($ordersTotal))
                ->description('Sum of order totals')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),

        //    Stat::make('orders_last7', 'Last 7d')
        //         ->value((string) $ordersLast7)
        //         ->description('Orders in last 7 days')
        //         ->icon('heroicon-o-clock')
        //         ->color('warning'),
        ];
    }

    protected function fallbackStats(): array
    {
        return [
            Stat::make('orders_count', 'Orders')->value('0')->description('No orders')->icon('heroicon-o-exclamation-triangle')->color('danger'),
            Stat::make('orders_total', 'Orders Total')->value('₱0.00')->description('No data')->icon('heroicon-o-currency-dollar')->color('primary'),
        //    Stat::make('orders_last7', 'Last 7d')->value('0')->description('No data')->icon('heroicon-o-clock')->color('warning'), // Not Included 
        ];
    }
}
