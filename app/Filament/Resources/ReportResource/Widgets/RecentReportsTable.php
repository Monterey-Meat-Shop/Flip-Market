<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Report;
use App\Models\ReportMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class RecentReportsTable extends Widget
{
    protected static string $view = 'filament.widgets.recent-reports-table';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $rows = Report::query()
            ->select(['id', 'title', 'status', 'report_date', 'created_at', 'is_visible'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        // persist today's aggregated metrics (lightweight, cached to avoid duplicate writes)
        $today = today()->toDateString();
        if (Schema::hasTable('report_metrics')) {
            $cacheKey = "report_metrics:stored:{$today}";
            if (! Cache::has($cacheKey)) {
                $metrics = $this->collectMetrics();
                ReportMetric::updateOrCreate(
                    ['metric_date' => $today],
                    [
                        'orders_count' => $metrics['orders_count'],
                        'orders_total' => $metrics['orders_total'],
                        'orders_last7' => $metrics['orders_last7'],
                        'payments_count' => $metrics['payments_count'] ?? 0,
                        'payments_total' => $metrics['payments_total'] ?? 0,
                        'meta' => $metrics['meta'] ?? null,
                    ]
                );
                Cache::put($cacheKey, true, 300); // 5 minutes
            }
        }

        return [
            'reports' => $rows,
        ];
    }

    protected function collectMetrics(): array
    {
        $ordersCount = 0;
        $ordersTotal = 0.0;
        $ordersLast7 = 0;
        $paymentsCount = 0;
        $paymentsTotal = 0.0;

        if (Schema::hasTable('orders')) {
            $ordersCount = (int) DB::table('orders')->count();

            foreach (['total','total_amount','amount','grand_total'] as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $ordersTotal = (float) DB::table('orders')->sum($c);
                    break;
                }
            }

            $ordersLast7 = (int) DB::table('orders')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count();
        }

        if (Schema::hasTable('payments')) {
            foreach (['amount','total','paid_amount'] as $c) {
                if (Schema::hasColumn('payments', $c)) {
                    $paymentsTotal = (float) DB::table('payments')->sum($c);
                    break;
                }
            }
            $paymentsCount = (int) (Schema::hasTable('payments') ? DB::table('payments')->count() : 0);
        }

        return [
            'orders_count' => $ordersCount,
            'orders_total' => $ordersTotal,
            'orders_last7' => $ordersLast7,
            'payments_count' => $paymentsCount,
            'payments_total' => $paymentsTotal,
            'meta' => ['collected_at' => now()->toDateTimeString()],
        ];
    }
}