<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Exports\ReportsExport;
use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListReports extends Page
{
    protected static string $resource = ReportResource::class;
    protected static string $view = 'filament.resources.report-resource.pages.list-reports';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    Forms\Components\TextInput::make('filename')
                        ->label('File Name')
                        ->default('weekly-report_' . now()->format('Ymd_His'))
                        ->required()
                        ->helperText('File will be saved as .xlsx'),
                ])
                ->action(fn (array $data): BinaryFileResponse =>
                    Excel::download(new ReportsExport(), "{$data['filename']}.xlsx")
                ),

            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-printer')
                ->form([
                    Forms\Components\TextInput::make('filename')
                        ->label('File Name')
                        ->default('weekly-report_' . now()->format('Ymd_His'))
                        ->required()
                        ->helperText('File will be saved as .pdf'),
                ])
                ->action(function (array $data) {
                    $export = new ReportsExport();

                    $pdf = Pdf::loadView('pdf.weekly-report', [
                        'headings' => $export->headings(),
                        'rows'     => $export->array(),
                    ])->setPaper('A4', 'portrait');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        "{$data['filename']}.pdf"
                    );
                }),
        ];
    }
}
