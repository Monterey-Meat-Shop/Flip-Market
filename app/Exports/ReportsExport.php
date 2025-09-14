<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ReportsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startOfWeek;
    protected $endOfWeek;
    protected $orderAmountColumn;
    protected $paymentAmountColumn;

    public function __construct()
    {
        $this->startOfWeek = Carbon::now()->startOfWeek();
        $this->endOfWeek = Carbon::now()->endOfWeek();
        
        // Detect amount columns
        if (class_exists(Order::class) && Schema::hasTable((new Order())->getTable())) {
            $orderTable = (new Order())->getTable();
            foreach (['total', 'total_amount', 'amount', 'grand_total'] as $col) {
                if (Schema::hasColumn($orderTable, $col)) {
                    $this->orderAmountColumn = $col;
                    break;
                }
            }
        }

        if (class_exists(Payment::class) && Schema::hasTable((new Payment())->getTable())) {
            $paymentTable = (new Payment())->getTable();
            foreach (['amount', 'total', 'paid_amount'] as $col) {
                if (Schema::hasColumn($paymentTable, $col)) {
                    $this->paymentAmountColumn = $col;
                    break;
                }
            }
        }
    }

    public function collection()
    {
        $data = collect();

        // Add Orders data
        if (class_exists(Order::class) && Schema::hasTable((new Order())->getTable())) {
            $orders = Order::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($orders as $order) {
                $data->push([
                    'type' => 'Order',
                    'id' => $order->id,
                    'date' => $order->created_at->format('Y-m-d H:i:s'),
                    'amount' => $this->orderAmountColumn ? $order->{$this->orderAmountColumn} : 0,
                    'status' => $order->status ?? 'N/A',
                    'reference' => $order->order_number ?? $order->id,
                    'customer' => $order->customer_name ?? $order->user->name ?? 'N/A',
                ]);
            }
        }

        // Add Payments data
        if (class_exists(Payment::class) && Schema::hasTable((new Payment())->getTable())) {
            $payments = Payment::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($payments as $payment) {
                $data->push([
                    'type' => 'Payment',
                    'id' => $payment->id,
                    'date' => $payment->created_at->format('Y-m-d H:i:s'),
                    'amount' => $this->paymentAmountColumn ? $payment->{$this->paymentAmountColumn} : 0,
                    'status' => $payment->status ?? 'N/A',
                    'reference' => $payment->reference_number ?? $payment->transaction_id ?? $payment->id,
                    'customer' => $payment->customer_name ?? $payment->user->name ?? 'N/A',
                ]);
            }
        }

        return $data->sortByDesc('date');
    }

    public function headings(): array
    {
        return [
            'Type',
            'ID',
            'Date',
            'Amount (₱)',
            'Status',
            'Reference',
            'Customer',
        ];
    }

    public function map($item): array
    {
        return [
            $item['type'],
            $item['id'],
            $item['date'],
            '₱' . number_format((float)$item['amount'], 2),
            $item['status'],
            $item['reference'],
            $item['customer'],
        ];
    }

    // Helper method for PDF generation
    public function array(): array
    {
        return $this->collection()->map(function ($item) {
            return $this->map($item);
        })->toArray();
    }

    // Get summary statistics for PDF
    public function getWeeklySummary(): array
    {
        $ttl = 60;

        // Orders Stats
        $ordersCount = 0;
        $ordersTotal = 0;
        if (class_exists(Order::class) && Schema::hasTable((new Order())->getTable())) {
            $ordersCount = Cache::remember("export:orders_count", $ttl, function () {
                return Order::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])->count();
            });

            if ($this->orderAmountColumn) {
                $ordersTotal = Cache::remember("export:orders_total", $ttl, function () {
                    return Order::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
                        ->sum($this->orderAmountColumn);
                });
            }
        }

        // Payments Stats
        $paymentsCount = 0;
        $paymentsTotal = 0;
        $paymentsPending = 0;
        if (class_exists(Payment::class) && Schema::hasTable((new Payment())->getTable())) {
            $paymentTable = (new Payment())->getTable();
            
            $paymentsCount = Cache::remember("export:payments_count", $ttl, function () {
                return Payment::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])->count();
            });

            if ($this->paymentAmountColumn) {
                $paymentsTotal = Cache::remember("export:payments_total", $ttl, function () {
                    return Payment::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
                        ->sum($this->paymentAmountColumn);
                });
            }

            if (Schema::hasColumn($paymentTable, 'status')) {
                $paymentsPending = Cache::remember("export:payments_pending", $ttl, function () {
                    return Payment::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
                        ->where('status', 'pending')
                        ->count();
                });
            }
        }

        return [
            'period_start' => $this->startOfWeek->format('M j, Y'),
            'period_end' => $this->endOfWeek->format('M j, Y'),
            'orders_count' => $ordersCount,
            'orders_total' => $ordersTotal,
            'payments_count' => $paymentsCount,
            'payments_total' => $paymentsTotal,
            'payments_pending' => $paymentsPending,
            'total_records' => $ordersCount + $paymentsCount,
        ];
    }
}