<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Exports\ReportsExport;
use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListReports extends Page
{
    protected static string $resource = ReportResource::class;

    // custom blade, renders only widgets (no table)
    protected static string $view = 'filament.resources.report-resource.pages.list-reports';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
              //  ->icon('heroicon-o-document-text')
                ->action('exportPdf'),
        ];
    }

    // public so Livewire / Filament can call it
    public function exportPdf(): BinaryFileResponse
    {
        // Use the PDF driver. You can use MPDF, DOMPDF, or TCPDF depending on which you installed
        return Excel::download(
            new ReportsExport(),
            'reports_' . now()->format('Ymd_His') . '.pdf',
            \Maatwebsite\Excel\Excel::MPDF
        );
    }
}
