<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DataReportsResource\Widgets\StockStatsWidget;
use App\Filament\Widgets\CategoryStock;
use App\Filament\Widgets\SalesReport;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ReportsExport;

class DataReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Inventory Report';

    protected static string $view = 'filament.pages.data-reports';

protected function getHeaderWidgets(): array
    {

    //Chart Object Here
        return [
            StockStatsWidget::class,
            SalesReport::class, 
            CategoryStock::class,
        ];

    }
protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    TextInput::make('filename')
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
