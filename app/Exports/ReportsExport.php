<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportsExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        // Single row, all “hello world”
        return [
            [
                'hello world',
                'hello world',
                'hello world',
                'hello world',
                'hello world',
                'hello world',
                'hello world',
            ]
        ];
    }

    public function headings(): array
    {
        // Also all “hello world”
        return [
            'hello world',
            'hello world',
            'hello world',
            'hello world',
            'hello world',
            'hello world',
            'hello world',
        ];
    }
}
