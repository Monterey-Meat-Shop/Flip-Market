<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Exports\ReportsExport;
use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListReports extends Page
{
    protected static string $resource = ReportResource::class;

    protected static string $view = 'filament.resources.report-resource.pages.list-reports';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                // open a form/modal to ask for filename
                ->form([
                    Forms\Components\TextInput::make('filename')
                        ->label('File name')
                        ->default('reports_' . now()->format('Ymd_His'))
                        ->required()
                        ->helperText('Without extension (".pdf" will be added)'),
                ])
                ->action(function (array $data): BinaryFileResponse {
                    $filename = $data['filename'] . '.pdf';
                    return Excel::download(
                        new ReportsExport(),
                        $filename,
                        \Maatwebsite\Excel\Excel::MPDF
                    );
                }),
        ];
    }

}