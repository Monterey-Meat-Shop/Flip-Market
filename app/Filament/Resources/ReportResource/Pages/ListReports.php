<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Exports\ReportsExport;
use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-document-text')
                ->action('export'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ReportResource\Widgets\ReportStats::class,
            \App\Filament\Resources\ReportResource\Widgets\OrdersStats::class,
            \App\Filament\Resources\ReportResource\Widgets\PaymentsStats::class,
            \App\Filament\Resources\ReportResource\Widgets\OrdersRealtimeStats::class,
        ];
    }

    // Export action handler (must be public so the Livewire component can call it)
    public function export(): BinaryFileResponse
    {
        return Excel::download(new ReportsExport(), 'reports.xlsx');
    }
}
