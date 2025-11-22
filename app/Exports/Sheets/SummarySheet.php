<?php

namespace App\Exports\Sheets;

use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;
    protected $period;

    public function __construct($startDate, $endDate, $period)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $summary = $this->getSummary();
        $reportTitle = match ($this->period) {
            'monthly' => 'Monthly Sales & Orders Report',
            'yearly' => 'Yearly Sales & Orders Report',
            default => 'Weekly Sales & Orders Report',
        };

        return [
            ['FLIP MARKET'],
            [$reportTitle],
            ['Generated: ' . now()->format('F j, Y g:i A')],
            ['Period: ' . $this->startDate->format('M j') . ' - ' . $this->endDate->format('M j, Y')],
            [''],
            ['METRIC', 'VALUE'],
            ['Total Orders', $summary['orders_count']],
            ['Total Order Amount', '₱' . number_format($summary['orders_total'], 2)],
            ['Total Payments', $summary['payments_count']],
            ['Total Payment Amount', '₱' . number_format($summary['payments_total'], 2)],
            ['Pending Payments', $summary['payments_pending']],
            ['Unique Customers', $summary['unique_customers']],
            ['Products Sold', $summary['total_products_sold']],
            ['Average Order Value', '₱' . number_format($summary['avg_order_value'], 2)],
            [''],
            ['TOTAL REVENUE', '₱' . number_format($summary['orders_total'] + $summary['payments_total'], 2)],
        ];
    }

    protected function getSummary(): array
    {
        $start = $this->startDate->copy()->startOfDay();
        $end = $this->endDate->copy()->endOfDay();

        $orders = Order::query()
            ->whereBetween('order_date', [$start, $end])
            ->where('order_status', 'completed')
            ->whereHas('payment', fn($q) => $q->whereIn('status', ['paid', 'verified']))
            ->get();

        $payments = Payment::query()
    ->whereBetween('created_at', [$start, $end])
    ->where('status', 'paid')
    ->whereHas('order', fn($q) => $q->where('order_status', 'completed'))
    ->get();

        $pendingPayments = Payment::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'pending')
            ->count();

        $uniqueCustomers = Order::query()
    ->whereBetween('order_date', [$start, $end])
    ->where('order_status', 'completed')
    ->with('customer') // eager load
    ->get()
    ->pluck('customer.user_id') // get user_id from related customer
    ->unique()
    ->count();

        $totalProductsSold = OrderItem::query()
            ->whereHas('order', fn($q) => $q
                ->whereBetween('order_date', [$start, $end])
                ->where('order_status', 'completed'))
            ->sum('quantity');

        $ordersCount = $orders->count();
        $ordersTotal = $orders->sum('final_amount');

        return [
            'orders_count' => $ordersCount,
            'orders_total' => $ordersTotal,
            'payments_count' => $payments->count(),
            'payments_total' => $payments->sum('amount'),
            'payments_pending' => $pendingPayments,
            'unique_customers' => $uniqueCustomers,
            'total_products_sold' => $totalProductsSold,
            'avg_order_value' => $ordersCount > 0 ? $ordersTotal / $ordersCount : 0,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 25];
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge header cells
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A4:B4');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            '3:4' => [
                'font' => ['size' => 10, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            ],
            16 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            ],
            'A6:B16' => [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                ],
            ],
        ];
    }
}