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

    // custom blade that renders only widgets (no table)
    protected static string $view = 'filament.resources.report-resource.pages.list-reports';

    protected function getHeaderActions(): array
    {
        return [
            // no CreateAction — removes the "New report" button
            Actions\Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-document-text')
                ->action('export'),
        ];
    }

    // public so Livewire/Filament can call it
    public function export(): BinaryFileResponse
    {
        return Excel::download(new ReportsExport(), 'reports.xlsx');
    }
}
