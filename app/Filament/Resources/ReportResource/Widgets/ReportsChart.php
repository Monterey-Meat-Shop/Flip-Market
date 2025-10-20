<?php


namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ReportsChart extends Widget
{
    // full width chart under the widgets row
    protected static string $view = 'filament.resources.report-resource.widgets.reports-chart';
    protected int | string | array $columnSpan = 'full';

    // data exposed to the blade view
    public array $labels = [];
    public array $ordersSeries = [];
    public array $paymentsSeries = [];

    public function mount(): void
    {
        $this->buildSeries();
    }

    protected function buildSeries(): void
    {
        $labels = [];
        $orders = [];
        $payments = [];

        // days: last 7 days (including today)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('M j');

            // orders: count (or sum if you prefer)
            $orders[] = $this->queryOrdersDay($date);

            // payments: sum of amount-like column (fallback to count if no amount)
            $payments[] = $this->queryPaymentsDay($date);
        }

        $this->labels = $labels;
        $this->ordersSeries = $orders;
        $this->paymentsSeries = $payments;
    }

    protected function queryOrdersDay(string $date)
    {
        if (! Schema::hasTable('orders')) {
            return 0;
        }

        // prefer sum of amount if column exists, otherwise count
        foreach (['total_amount', 'total', 'amount', 'grand_total'] as $c) {
            if (Schema::hasColumn('orders', $c)) {
                return (float) DB::table('orders')->whereDate('created_at', $date)->sum($c);
            }
        }

        return (int) DB::table('orders')->whereDate('created_at', $date)->count();
    }

    protected function queryPaymentsDay(string $date)
    {
        if (! Schema::hasTable('payments')) {
            return 0;
        }

        // prefer sum of amount-like column, otherwise count
        foreach (['amount', 'total', 'paid_amount'] as $c) {
            if (Schema::hasColumn('payments', $c)) {
                return (float) DB::table('payments')->whereDate('created_at', $date)->sum($c);
            }
        }

        return (int) DB::table('payments')->whereDate('created_at', $date)->count();
    }
}