<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class PaymentsStats extends BaseWidget
{
    //protected ?string $heading = 'Payments Overview';

    protected function getStats(): array
    {
        $cacheTtl = 60;
        $formatCurrency = fn($v) => '₱' . number_format((float) $v, 2);

        $paymentsCount = 0;
        $paymentsTotal = 0;
        $paymentsPending = 0;
        $paymentsFailed = 0;
        $paymentsRefunded = 0;

        try {
            if (! class_exists(Payment::class) || ! Schema::hasTable((new Payment())->getTable())) {
                return [
                    Stat::make('payments_count', 'Payments')->value('0')->description('Total payments'),
                ];
            }

            $table = (new Payment())->getTable();

            $amountColumn = Cache::remember("reportstats:payments_amount_column", $cacheTtl, function () use ($table) {
                foreach (['amount', 'total', 'paid_amount'] as $c) {
                    if (Schema::hasColumn($table, $c)) {
                        return $c;
                    }
                }
                return null;
            });

            if (Schema::hasColumn($table, 'status')) {
                $paymentsPending = (int) Cache::remember("reportstats:payments_pending", $cacheTtl, fn() => DB::table($table)->where('status', 'pending')->count());
                $totalRows = (int) Cache::remember("reportstats:payments_total_rows", $cacheTtl, fn() => DB::table($table)->count());
                $paymentsCount = max(0, $totalRows - $paymentsPending);

                if ($amountColumn) {
                    $paymentsTotal = (float) Cache::remember("reportstats:payments_total_excluding_pending", $cacheTtl, fn() => DB::table($table)->where('status', '<>', 'pending')->sum($amountColumn));
                }

                $paymentsFailed = (int) Cache::remember("reportstats:payments_failed", $cacheTtl, fn() => DB::table($table)->where('status', 'failed')->count());
                $paymentsRefunded = (int) Cache::remember("reportstats:payments_refunded", $cacheTtl, fn() => DB::table($table)->where('status', 'refunded')->count());
            } else {
                // fallback: include all if no status column
                $paymentsCount = (int) Cache::remember("reportstats:payments_count", $cacheTtl, fn() => DB::table($table)->count());
                if ($amountColumn) {
                    $paymentsTotal = (float) Cache::remember("reportstats:payments_total", $cacheTtl, fn() => DB::table($table)->sum($amountColumn));
                    $paymentsRefunded = (int) Cache::remember("reportstats:payments_refunded_negative", $cacheTtl, fn() => DB::table($table)->where($amountColumn, '<', 0)->count());
                }
            }
        } catch (Exception $e) {
            $paymentsCount = $paymentsTotal = $paymentsPending = $paymentsFailed = $paymentsRefunded = 0;
        }

        return [
            // Stat::make('payments_count', 'Payments (excl. pending)')->value((string) $paymentsCount)->description('Payments excluding pending'),
            // Stat::make('payments_total', 'Payments Total')->value($formatCurrency($paymentsTotal))->description('Sum of payments excluding pending'),
            // Stat::make('payments_pending', 'Pending')->value((string) $paymentsPending)->description('Payments with status "pending"'),
            // Stat::make('payments_failed', 'Failed')->value((string) $paymentsFailed)->description('Failed payments'),
            // Stat::make('payments_refunded', 'Refunded')->value((string) $paymentsRefunded)->description('Refunded payments'),
        ];
    }
}