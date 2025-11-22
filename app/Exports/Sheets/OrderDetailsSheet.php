<?php

namespace App\Exports\Sheets;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OrderDetailsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Order Details';
    }

    public function array(): array
    {
        $orders = Order::with('customer', 'orderItems.product', 'payment')
            ->whereBetween('order_date', [$this->startDate, $this->endDate])
            ->get();

        $data = [
            [
                'Order ID',
                'Customer Name',
                'Order Date',
                'Total Amount',
                'Payment Status',
                'Order Status',
            ]
        ];

        foreach ($orders as $order) {
            $data[] = [
                $order->orderID,
                $order->customer?->full_name ?? '—',
                $order->order_date->format('Y-m-d H:i'),
                '₱' . number_format($order->final_amount, 2),
                $order->payment?->status ?? 'No Payment',
                ucfirst($order->order_status),
            ];
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
