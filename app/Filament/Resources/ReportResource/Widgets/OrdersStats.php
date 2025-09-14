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
    protected ?string $heading = 'Weekly Orders Report';

    protected function getStats(): array
    {
        $ttl = 60; // cache for 1 min

        if (! class_exists(Order::class)) {
            return $this->fallbackStats();
        }

        $table = (new Order())->getTable();
        if (! Schema::hasTable($table)) {
            return $this->fallbackStats();
        }

        $amountColumn = Cache::remember("orders_stats:amount_column", $ttl, function () use ($table) {
            foreach (['total', 'total_amount', 'amount', 'grand_total'] as $c) {
                if (Schema::hasColumn($table, $c)) {
                    return $c;
                }
            }
            return null;
        });

        // Calculate current week range
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        // Calculate last week range
        $startOfLastWeek = (clone $startOfWeek)->subWeek();
        $endOfLastWeek   = (clone $endOfWeek)->subWeek();

        // Weekly Orders Count
        $ordersThisWeek = (int) Cache::remember("orders_stats:this_week", $ttl, function () use ($table, $startOfWeek, $endOfWeek) {
            return DB::table($table)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count();
        });

        $ordersLastWeek = (int) Cache::remember("orders_stats:last_week", $ttl, function () use ($table, $startOfLastWeek, $endOfLastWeek) {
            return DB::table($table)
                ->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
                ->count();
        });

        // Weekly Sales Total
        $salesThisWeek = 0.0;
        $salesLastWeek = 0.0;
        if ($amountColumn) {
            $salesThisWeek = (float) Cache::remember("orders_stats:sales_this_week", $ttl, function () use ($table, $amountColumn, $startOfWeek, $endOfWeek) {
                return DB::table($table)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sum($amountColumn);
            });

            $salesLastWeek = (float) Cache::remember("orders_stats:sales_last_week", $ttl, function () use ($table, $amountColumn, $startOfLastWeek, $endOfLastWeek) {
                return DB::table($table)
                    ->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
                    ->sum($amountColumn);
            });
        }

        $formatCurrency = fn($v) => '₱' . number_format((float) $v, 2);

        // Calculate week-over-week change (%)
        $ordersGrowth = $ordersLastWeek > 0 
            ? (($ordersThisWeek - $ordersLastWeek) / $ordersLastWeek) * 100 
            : 0;

        $salesGrowth = $salesLastWeek > 0
            ? (($salesThisWeek - $salesLastWeek) / $salesLastWeek) * 100
            : 0;

        return [
            Stat::make('orders_weekly', 'Orders (This Week)')
                ->value((string) $ordersThisWeek)
                ->description($ordersGrowth >= 0 
                    ? '↑ ' . number_format($ordersGrowth, 1) . '% vs last week' 
                    : '↓ ' . number_format(abs($ordersGrowth), 1) . '% vs last week')
                ->icon('heroicon-o-shopping-cart')
                ->color($ordersGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('sales_weekly', 'Sales (This Week)')
                ->value($formatCurrency($salesThisWeek))
                ->description($salesGrowth >= 0 
                    ? '↑ ' . number_format($salesGrowth, 1) . '% vs last week' 
                    : '↓ ' . number_format(abs($salesGrowth), 1) . '% vs last week')
                ->icon('heroicon-o-currency-dollar')
                ->color($salesGrowth >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function fallbackStats(): array
    {
        return [
            Stat::make('orders_weekly', 'Orders (This Week)')
                ->value('0')
                ->description('No data')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('sales_weekly', 'Sales (This Week)')
                ->value('₱0.00')
                ->description('No data')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),
        ];
    }
}
