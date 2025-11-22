<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Import all sheet classes
use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\FastMovingProductsSheet;
use App\Exports\Sheets\TopCustomersSheet;
use App\Exports\Sheets\OrderDetailsSheet;

class ReportsExport implements WithMultipleSheets
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
            $this->startDate = Carbon::create($year, $month)->startOfMonth();
            $this->endDate = Carbon::create($year, $month)->endOfMonth();
        } elseif ($period === 'yearly') {
            $this->startDate = Carbon::create($year, 1, 1)->startOfYear();
            $this->endDate = Carbon::create($year, 12, 31)->endOfYear();
        } else {
            $this->startDate = Carbon::now()->startOfWeek();
            $this->endDate = Carbon::now()->endOfWeek();
        }
    }

    public function sheets(): array
    {
        return [
            new SummarySheet($this->startDate, $this->endDate, $this->period),
            new FastMovingProductsSheet($this->startDate, $this->endDate),
            new TopCustomersSheet($this->startDate, $this->endDate),
            new OrderDetailsSheet($this->startDate, $this->endDate),
        ];
    }
}
