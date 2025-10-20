<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdersSalesChart extends Widget
{
    protected static string $view = 'filament.resources.report-resource.widgets.orders-sales-chart';
    protected int | string | array $columnSpan = 'full';

    public array $labels = [];
    public array $orders = [];
    public array $sales = [];

    public int $totalOrders = 0;
    public float $totalSales = 0.0;

    public bool $salesUp = false;
    public bool $ordersUp = false;

    public function mount(): void
    {
        $this->buildSeries();
    }

    protected function buildSeries(): void
    {
        $cacheKey = 'reports:orders_sales_chart_weekly';
        [$labels, $orders, $sales] = Cache::remember($cacheKey, 300, function () {
            $labels = $orders = $sales = [];

            $best = $this->detectBestTable();

            // --- Generate last 12 weeks ---
            $start = Carbon::now()->startOfWeek()->subWeeks(11);

            for ($i = 0; $i < 12; $i++) {
                $weekStart = (clone $start)->addWeeks($i);
                $weekEnd = $weekStart->copy()->endOfWeek();

                $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');

                // Orders Count
                $ordersCount = 0;
                if (Schema::hasTable('orders')) {
                    $ordersDateCol = $this->detectDateColumn('orders') ?? 'created_at';
                    $ordersCount = DB::table('orders')
                        ->whereBetween($ordersDateCol, [$weekStart, $weekEnd])
                        ->count();
                }
                $orders[] = $ordersCount;

                // Sales Total
                $salesTotal = 0.0;
                if ($best) {
                    $tbl = $best['table'];
                    $amt = $best['amount'];
                    $dtCol = $best['date'] ?? null;

                    $query = DB::table($tbl);
                    if ($dtCol && Schema::hasColumn($tbl, $dtCol)) {
                        $query->whereBetween($dtCol, [$weekStart, $weekEnd]);
                    }
                    $salesTotal = (float) $query->sum($amt);
                }
                $sales[] = $salesTotal;
            }

            return [$labels, $orders, $sales];
        });

        $this->labels = $labels;
        $this->orders = $orders;
        $this->sales = $sales;

        $this->totalOrders = array_sum($orders);
        $this->totalSales = array_sum($sales);

        $this->salesUp = $this->compareTrend($this->sales);
        $this->ordersUp = $this->compareTrend($this->orders);
    }

    /** Detects best source table + amount column + date column */
    protected function detectBestTable(): ?array
    {
        $tables = ['payments', 'sales', 'orders'];
        $amountCandidates = [
            'orders_total','amount','total','paid_amount','total_amount','grand_total',
            'total_price','amount_paid','price','subtotal','net_amount'
        ];
        $dateCandidates = ['created_at','created','date','paid_at','paid_on','payment_date','order_date','placed_at'];

        $best = null;
        $bestTotal = 0;

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) continue;

            foreach ($amountCandidates as $amountCol) {
                if (! Schema::hasColumn($table, $amountCol)) continue;

                $dateCol = null;
                foreach ($dateCandidates as $dc) {
                    if (Schema::hasColumn($table, $dc)) {
                        $dateCol = $dc;
                        break;
                    }
                }

                try {
                    $total = (float) DB::table($table)->sum($amountCol);
                } catch (\Throwable $e) {
                    $total = 0;
                }

                if ($total > $bestTotal) {
                    $bestTotal = $total;
                    $best = ['table' => $table, 'amount' => $amountCol, 'date' => $dateCol];
                }
            }
        }
        return $best;
    }

    protected function detectDateColumn(string $table): ?string
    {
        $candidates = ['created_at','created','date','paid_at','paid_on','payment_date','order_date','placed_at'];
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    protected function compareTrend(array $data): bool
    {
        if (count($data) < 2) return false;
        $last = $data[count($data) - 1];
        $prev = $data[count($data) - 2] ?: 0;
        return $last >= $prev;
    }

    // /** Export report as PDF */
    // public function exportToPdf()
    // {
    //     $pdf = Pdf::loadView('pdf.weekly-report', [
    //         'labels' => $this->labels,
    //         'orders' => $this->orders,
    //         'sales' => $this->sales,
    //         'totalOrders' => $this->totalOrders,
    //         'totalSales' => $this->totalSales,
    //     ]);

    //     return response()->streamDownload(function () use ($pdf) {
    //         echo $pdf->stream();
    //     }, 'weekly-report-' . now()->format('Y-m-d') . '.pdf');
    // }
}
