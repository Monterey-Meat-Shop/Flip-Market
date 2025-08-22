<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ReportMetric;

class OrdersRealtimeStats extends BaseWidget
{
    protected ?string $heading = 'Realtime Orders Stats';

    protected function getStats(): array
    {
        $formatCurrency = fn($v) => '₱' . number_format((float) $v, 2);

        // prefer latest persisted metric
        $ordersCount = 0;
        $ordersTotal = 0.0;
        $ordersLast7 = 0;

        if (Schema::hasTable('report_metrics')) {
            $metric = ReportMetric::orderByDesc('metric_date')->first();
            if ($metric) {
                $ordersCount = $metric->orders_count;
                $ordersTotal = $metric->orders_total;
                $ordersLast7 = $metric->orders_last7 ?? 0;
            }
        }

        // fallback to live read if no persisted metric
        if (($ordersCount === 0 && $ordersTotal === 0.0) && Schema::hasTable('orders')) {
            $ordersCount = (int) DB::table('orders')->count();
            $ordersLast7 = (int) DB::table('orders')->whereDate('created_at', '>=', now()->subDays(7))->count();

            foreach (['total','total_amount','amount','grand_total'] as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $ordersTotal = (float) DB::table('orders')->sum($c);
                    break;
                }
            }
        }

        return [
            Stat::make('orders_count', 'Total Orders')->value((string) $ordersCount)->description('Count of all orders')->icon('heroicon-o-shopping-cart')->color('primary'),
            Stat::make('orders_total', 'Total Sales')->value($formatCurrency($ordersTotal))->description('Sum of order totals')->icon('heroicon-o-currency-dollar')->color('success'),
           // Stat::make('orders_last7', 'Orders (7d)')->value((string) $ordersLast7)->description('Orders in last 7 days')->icon('heroicon-o-clock')->color('warning'),
        ];
    }
}