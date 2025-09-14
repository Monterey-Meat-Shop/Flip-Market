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
            // Excel Export Button
            // Actions\Action::make('export_excel')
            //     ->label('Export Excel')
            //     ->icon('heroicon-o-document-arrow-down')
            //     ->color('success')
            //     ->form([
            //         Forms\Components\TextInput::make('filename')
            //             ->label('File Name')
            //             ->default('weekly-sales-report_' . now()->format('Ymd_His'))
            //             ->required()
            //             ->helperText('Do not include ".xlsx" — it will be added automatically.')
            //             ->maxLength(100),
            //     ])
            //     ->action(fn (array $data): BinaryFileResponse =>
            //         Excel::download(
            //             new ReportsExport(),
            //             $data['filename'] . '.xlsx'
                //     )
                // ),

            // PDF Export Button
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Forms\Components\TextInput::make('filename')
                        ->label('File Name')
                        ->default('weekly-sales-report_' . now()->format('Ymd_His'))
                        ->required()
                        ->helperText('Do not include ".pdf" — it will be added automatically.')
                        ->maxLength(100),
                ])
                ->action(function (array $data) {
                    $export = new ReportsExport();
                    $summary = $export->getWeeklySummary();

                    $pdf = Pdf::loadView('pdf.weekly-report', [
                        'headings' => $export->headings(),
                        'rows' => $export->array(),
                        'summary' => $summary,
                    ])->setPaper('A4', 'portrait');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $data['filename'] . '.pdf'
                    );
                }),
        ];
    }
}