<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Report; // Adjust this to your actual model
use Illuminate\Support\Collection;

class ReportsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Replace this with your actual data logic
        return Report::all(); // or whatever query you need
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Status',
            'Created At',
            'Updated At',
            // Add your actual column headers here
        ];
    }

    public function map($report): array
    {
        return [
            $report->id,
            $report->title,
            $report->description,
            $report->status,
            $report->created_at,
            $report->updated_at,
            // Map your actual report fields here
        ];
    }

    // Helper method for PDF generation
    public function array(): array
    {
        return $this->collection()->map(function ($report) {
            return $this->map($report);
        })->toArray();
    }
}