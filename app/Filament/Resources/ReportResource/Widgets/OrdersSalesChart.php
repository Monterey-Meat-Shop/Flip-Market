<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrdersSalesChart extends Widget
{
    protected static string $view = 'filament.resources.report-resource.widgets.orders-sales-chart';
    protected int | string | array $columnSpan = 'full';

    // exposed to the blade
    public array $labels = [];
    public array $orders = [];
    public array $sales = [];

    public function mount(): void
    {
        $this->buildSeries();
    }

    protected function buildSeries(): void
    {
        $cacheKey = 'reports:orders_sales_chart_monthly';
        [$labels, $orders, $sales] = Cache::remember($cacheKey, 300, function () {
            $labels = [];
            $orders = [];
            $sales = [];

            // build last 12 months windows
            $start = Carbon::now()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $dt = (clone $start)->addMonths($i);
                $periodStart = $dt->copy()->startOfMonth()->toDateTimeString();
                $periodEnd = $dt->copy()->endOfMonth()->toDateTimeString();
                $labels[] = $dt->format('M Y');

                // orders count for month
                $ordersCount = 0;
                if (Schema::hasTable('orders')) {
                    $ordersCount = (int) DB::table('orders')
                        ->whereBetween('created_at', [$periodStart, $periodEnd])
                        ->count();
                }
                $orders[] = $ordersCount;

                // sales total for month: try payments -> sales -> orders amount fallback
                $salesTotal = 0.0;
                if (Schema::hasTable('payments')) {
                    $amountCol = $this->detectColumn('payments', ['amount', 'total', 'paid_amount']);
                    if ($amountCol) {
                        $salesTotal = (float) DB::table('payments')
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                            ->sum($amountCol);
                        $sales[] = $salesTotal;
                        continue;
                    }
                }
                if (Schema::hasTable('sales')) {
                    $amountCol = $this->detectColumn('sales', ['amount', 'total']);
                    if ($amountCol) {
                        $salesTotal = (float) DB::table('sales')
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                            ->sum($amountCol);
                        $sales[] = $salesTotal;
                        continue;
                    }
                }
                // fallback: sum from orders table amount-like column
                if (Schema::hasTable('orders')) {
                    $amountCol = $this->detectColumn('orders', ['total_amount', 'total', 'amount', 'grand_total']);
                    if ($amountCol) {
                        $salesTotal = (float) DB::table('orders')
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                            ->sum($amountCol);
                    }
                }
                $sales[] = $salesTotal;
            }

            return [$labels, $orders, $sales];
        });

        $this->labels = $labels;
        $this->orders = $orders;
        $this->sales = $sales;
    }

    protected function detectColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) {
                return $c;
            }
        }
        return null;
    }
}