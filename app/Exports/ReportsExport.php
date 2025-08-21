<?php


namespace App\Exports;

use App\Models\Report;
use App\Models\ReportMetric;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportsExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $rows = [];

        // latest metrics summary row
        $metric = null;
        if (class_exists(ReportMetric::class)) {
            $metric = ReportMetric::orderByDesc('metric_date')->first();
        }

        $rows[] = [
            'ID' => 'SUMMARY',
            'Title' => 'Totals (latest)',
            'Status' => '',
            'Order Count' => $metric ? $metric->orders_count : 0,
            'Sales Total' => $metric ? $metric->orders_total : 0,
            'Orders (7d)' => $metric ? $metric->orders_last7 : 0,
            'Created At' => $metric ? $metric->metric_date->toDateString() : now()->toDateString(),
        ];

        // append reports
        $reports = Report::query()->orderBy('id')->get(['id','title','status','created_at']);
        foreach ($reports as $r) {
            $rows[] = [
                'ID' => $r->id,
                'Title' => $r->title,
                'Status' => $r->status,
                'Order Count' => '', // per-report aggregation not available by default
                'Sales Total' => '',
                'Orders (7d)' => '',
                'Created At' => $r->created_at ? $r->created_at->toDateTimeString() : null,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Status',
            'Order Count',
            'Sales Total',
            'Orders (7d)',
            'Created At',
        ];
    }
}