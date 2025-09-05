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

    // new: totals exposed to blade
    public int $totalOrders = 0;
    public float $totalSales = 0.0;

    // design-only flags (no algorithmic changes to series)
    public bool $salesUp = false;
    public bool $ordersUp = false;

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

            // --- detect best source table/amount/date once (resilient detection) ---
            $best = null;
            $bestTotal = 0;
            $tables = ['payments', 'sales', 'orders'];
            $amountCandidates = ['orders_total','amount','total','paid_amount','total_amount','grand_total','total_price','amount_paid','price','subtotal','net_amount'];
            $dateCandidates = ['created_at','created','date','paid_at','paid_on','payment_date','order_date','placed_at','updated_at'];

            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) { continue; }
                foreach ($amountCandidates as $amountCol) {
                    if (! Schema::hasColumn($table, $amountCol)) { continue; }
                    // try to find a date column for this table
                    $dateCol = null;
                    foreach ($dateCandidates as $dc) {
                        if (Schema::hasColumn($table, $dc)) { $dateCol = $dc; break; }
                    }
                    // if date col missing it's still OK — compute total across whole table
                    try {
                        $total = (float) DB::table($table)->sum($amountCol);
                    } catch (\Throwable $e) {
                        $total = 0;
                    }
                    if ($total > $bestTotal) {
                        $bestTotal = $total;
                        $best = ['table' => $table, 'amount' => $amountCol, 'date' => $dateCol];
                    }
                    // if we already found a large total, break early for this table
                    if ($bestTotal > 0) { /* continue scanning to pick possibly larger */ }
                }
            }

            // --- build last 12 months windows ---
            $start = Carbon::now()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $dt = (clone $start)->addMonths($i);
                $periodStart = $dt->copy()->startOfMonth()->toDateTimeString();
                $periodEnd = $dt->copy()->endOfMonth()->toDateTimeString();
                $labels[] = $dt->format('M Y');

                // orders count (use detected date if present)
                $ordersCount = 0;
                if (Schema::hasTable('orders')) {
                    $ordersDateCol = $this->detectDateColumn('orders') ?? 'created_at';
                    if (Schema::hasColumn('orders', $ordersDateCol)) {
                        $ordersCount = (int) DB::table('orders')->whereBetween($ordersDateCol, [$periodStart, $periodEnd])->count();
                    } else {
                        $ordersCount = (int) DB::table('orders')->whereBetween('created_at', [$periodStart, $periodEnd])->count();
                    }
                }
                $orders[] = $ordersCount;

                // sales total: if we detected a best source, use it; otherwise fall back to previous detection
                $salesTotal = 0.0;
                if ($best !== null) {
                    $tbl = $best['table'];
                    $amt = $best['amount'];
                    $dtCol = $best['date'] ?? 'created_at';
                    if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, $amt) && Schema::hasColumn($tbl, $dtCol)) {
                        $salesTotal = (float) DB::table($tbl)->whereBetween($dtCol, [$periodStart, $periodEnd])->sum($amt);
                    } elseif (Schema::hasTable($tbl) && Schema::hasColumn($tbl, $amt)) {
                        // date column missing — sum across whole table month-agnostic (safer than zero)
                        $salesTotal = (float) DB::table($tbl)->sum($amt);
                    }
                } else {
                    // legacy fallback: try payments/sales/orders specific detection (unchanged behavior)
                    if (Schema::hasTable('payments')) {
                        $amountCol = $this->detectColumn('payments', ['amount', 'total', 'paid_amount']);
                        $dateCol = $this->detectDateColumn('payments') ?? 'created_at';
                        if ($amountCol && Schema::hasColumn('payments', $dateCol)) {
                            $salesTotal = (float) DB::table('payments')->whereBetween($dateCol, [$periodStart, $periodEnd])->sum($amountCol);
                            $sales[] = $salesTotal;
                            continue;
                        }
                    }
                    if (Schema::hasTable('sales')) {
                        $amountCol = $this->detectColumn('sales', ['amount', 'total']);
                        $dateCol = $this->detectDateColumn('sales') ?? 'created_at';
                        if ($amountCol && Schema::hasColumn('sales', $dateCol)) {
                            $salesTotal = (float) DB::table('sales')->whereBetween($dateCol, [$periodStart, $periodEnd])->sum($amountCol);
                            $sales[] = $salesTotal;
                            continue;
                        }
                    }
                    if (Schema::hasTable('orders')) {
                        $amountCol = $this->detectColumn('orders', ['orders_total','total_amount', 'total', 'amount', 'grand_total']);
                        $dateCol = $this->detectDateColumn('orders') ?? 'created_at';
                        if ($amountCol && Schema::hasColumn('orders', $dateCol)) {
                            $salesTotal = (float) DB::table('orders')->whereBetween($dateCol, [$periodStart, $periodEnd])->sum($amountCol);
                        }
                    }
                }

                $sales[] = $salesTotal;
            }

            return [$labels, $orders, $sales];
        });

        $this->labels = $labels;
        $this->orders = $orders;
        $this->sales = $sales;

        $this->totalOrders = array_sum($this->orders);
        $this->totalSales = array_sum($this->sales);

        if (count($this->sales) >= 2) {
            $last = $this->sales[count($this->sales) - 1];
            $prev = $this->sales[count($this->sales) - 2] ?: 0;
            $this->salesUp = $last >= $prev;
        }
        if (count($this->orders) >= 2) {
            $last = $this->orders[count($this->orders) - 1];
            $prev = $this->orders[count($this->orders) - 2] ?: 0;
            $this->ordersUp = $last >= $prev;
        }
    }

    // helper: detect numeric/amount column (existing)
    protected function detectColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) {
                return $c;
            }
        }
        return null;
    }

    // new: detect date/time column names commonly used
    protected function detectDateColumn(string $table): ?string
    {
        $candidates = ['created_at','created','date','paid_at','paid_on','payment_date','order_date','placed_at'];
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) {
                return $c;
            }
        }
        return null;
    }
}