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
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->action(function () {
                    // Fetch all products with variants, category, and brand
                    $products = Product::with(['variants', 'category', 'brand'])
                        ->orderBy('name')
                        ->get()
                        ->groupBy(fn($p) => $p->status); // group by stock status

                    // Render Blade view for the PDF
                    $pdf = Pdf::loadView('pdf.inventory-report', [
                        'groupedProducts' => $products,
                    ])->setPaper('A4', 'portrait');

                    // Stream download
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'inventory_report.pdf'
                    );
                })
                ->requiresConfirmation()
                ->modalHeading('Export Inventory Report')
                ->modalDescription('Generate a full inventory report PDF grouped by stock status.')
                ->modalSubmitActionLabel('Export')
                ->modalCancelActionLabel('Cancel'),
        ];
    }
}
