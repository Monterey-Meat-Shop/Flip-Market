<?php

namespace App\Exports\Sheets;

use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FastMovingProductsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
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
        return 'Fast Moving Products';
    }

    public function array(): array
    {
        $products = Product::with(['orderItems' => fn($q) => $q->whereHas('order', fn($o) => $o->whereBetween('order_date', [$this->startDate, $this->endDate])->where('order_status', 'completed'))])
            ->get()
            ->map(function ($product) {
                $totalSold = $product->orderItems->sum('quantity');
                return [
                    'Product Name' => $product->name,
                    'Total Sold' => $totalSold,
                ];
            })
            ->sortByDesc('Total Sold')
            ->take(10) // top 10 fast moving
            ->toArray();

        return array_merge([
            ['FLIP MARKET'],
            ['Fast Moving Products Report'],
            ['Period: ' . $this->startDate->format('M j, Y') . ' - ' . $this->endDate->format('M j, Y')],
            [''],
            ['Product Name', 'Total Sold'],
        ], $products);
    }

    public function columnWidths(): array
    {
        return ['A' => 40, 'B' => 15];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['size' => 10, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            5 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
