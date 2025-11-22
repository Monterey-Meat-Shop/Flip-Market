<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DataReportsResource\Widgets\StockStatsWidget;
use App\Filament\Widgets\CategoryStock;
use App\Filament\Widgets\SalesReport;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Barryvdh\DomPDF\Facade\Pdf;

class DataReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Inventory Report';

    protected static string $view = 'filament.pages.data-reports';

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return 'Reports';
        }
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockStatsWidget::class,
            SalesReport::class,
            CategoryStock::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
               ->label('Export Excel')
               ->icon('heroicon-o-document-arrow-down')
               ->color('success')
               ->action(function () {
                   $filename = 'inventory_report_' . now()->format('Ymd_His') . '.xlsx';

                   return \Maatwebsite\Excel\Facades\Excel::download(
                       new \App\Exports\Sheets\InventoryReportSheet(),
                       $filename
                   );
               })
               ->requiresConfirmation()
               ->modalHeading('Export Inventory Report')
               ->modalDescription('Generate a full inventory report grouped by stock status.')
               ->modalSubmitActionLabel('Export')
               ->modalCancelActionLabel('Cancel'),
        ];
    }
}
