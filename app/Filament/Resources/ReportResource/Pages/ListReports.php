<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Exports\ReportsExport;
use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Export Excel')
               ->icon('heroicon-m-arrow-down-tray')
                ->action('export'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ReportResource\Widgets\ReportStats::class,
            \App\Filament\Resources\ReportResource\Widgets\OrdersStats::class,
            \App\Filament\Resources\ReportResource\Widgets\PaymentsStats::class,
        ];
    }

    public function export(): StreamedResponse
    {
        return Excel::download(new ReportsExport(), 'reports.xlsx');
    }
}
