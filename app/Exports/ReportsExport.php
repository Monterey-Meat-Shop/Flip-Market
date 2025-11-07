<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportsExport implements FromArray, WithHeadings
{
    protected $startDate;
    protected $endDate;
    protected $period;

    public function __construct($period = 'weekly', $month = null, $year = null)
    {
        $this->period = $period;
        $year = $year ?? now()->year;

        if ($period === 'monthly') {
            $month = $month ?? now()->month;
            $this->startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $this->endDate = Carbon::create($year, $month, 1)->endOfMonth();
        } elseif ($period === 'yearly') {
            $this->startDate = Carbon::create($year, 1, 1)->startOfYear();
            $this->endDate = Carbon::create($year, 12, 31)->endOfYear();
        } else {
            $this->startDate = Carbon::now()->startOfWeek();
            $this->endDate = Carbon::now()->endOfWeek();
        }
    }

    public function getSummary()
    {
        $start = $this->startDate->copy()->startOfDay();
        $end = $this->endDate->copy()->endOfDay();

        // Get completed orders WITH payments (joined by orderID)
        $orders = Order::query()
            ->whereBetween('order_date', [$start, $end])
            ->where('order_status', 'completed')
            ->whereHas('payment', function ($query) {
                $query->whereIn('status', ['paid', 'verified']);
            })
            ->get();

        // Get all payments (paid) within the same date range
        $payments = Payment::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->get();

        // Count pending payments
        $pendingPayments = Payment::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'pending')
            ->count();

        // Calculate totals
        $totalOrders = $orders->count();
        $totalOrderAmount = $orders->sum('final_amount');
        $totalPaymentsCount = $payments->count();
        $totalPayments = $payments->sum('amount');

        // Titles
        $reportTitle = match ($this->period) {
            'monthly' => 'Monthly Sales & Orders Report',
            'yearly' => 'Yearly Sales & Orders Report',
            default => 'Weekly Sales & Orders Report',
        };

        $periodType = ucfirst($this->period);

        return [
            'orders_count' => $totalOrders,
            'orders_total' => $totalOrderAmount,
            'payments_count' => $totalPaymentsCount,
            'payments_total' => $totalPayments,
            'payments_pending' => $pendingPayments,
            'period_start' => $start->format('M j'),
            'period_end' => $end->format('M j, Y'),
            'report_title' => $reportTitle,
            'period_type' => $periodType,
        ];
    }

    public function array(): array
    {
        $summary = $this->getSummary();

        return [
            [
                'Total Orders',
                $summary['orders_count'],
                '₱' . number_format($summary['orders_total'], 2),
                '₱' . number_format($summary['payments_total'], 2),
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Description',
            'Count',
            'Total Order Amount',
            'Total Payments',
        ];
    }
}
