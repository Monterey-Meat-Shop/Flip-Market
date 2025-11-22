<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Actions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

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
                ->color('success')
                ->form([
                    Forms\Components\Select::make('period')
                        ->label('Report Type')
                        ->options([
                            'weekly' => 'Weekly Sales Report',
                            'monthly' => 'Monthly Sales Report',
                            'yearly' => 'Yearly Sales Report',
                        ])
                        ->default('weekly')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('month', null)),

                    Forms\Components\Select::make('month')
                        ->label('Select Month')
                        ->options([
                            '1' => 'January', '2' => 'February', '3' => 'March',
                            '4' => 'April', '5' => 'May', '6' => 'June',
                            '7' => 'July', '8' => 'August', '9' => 'September',
                            '10' => 'October', '11' => 'November', '12' => 'December',
                        ])
                        ->default(now()->month)
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('period') === 'monthly'),

                    Forms\Components\Select::make('year')
                        ->label('Select Year')
                        ->options(function () {
                            $years = [];
                            $currentYear = now()->year;
                            for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                $years[$i] = $i;
                            }
                            return $years;
                        })
                        ->default(now()->year)
                        ->required()
                        ->visible(fn (Forms\Get $get) => in_array($get('period'), ['monthly', 'yearly'])),

                    Forms\Components\TextInput::make('filename')
                        ->label('File Name')
                        ->default('sales-report_' . now()->format('Ymd_His'))
                        ->required()
                        ->maxLength(100)
                        ->helperText('Do not include ".xlsx" — it will be added automatically.'),
                ])
                ->action(function (array $data) {
                    $filename = $data['filename'] . '.xlsx';

                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ReportsExport(
                            $data['period'],
                            $data['month'] ?? null,
                            $data['year'] ?? null
                        ),
                        $filename
                    );
                }),
        ];
    }
}