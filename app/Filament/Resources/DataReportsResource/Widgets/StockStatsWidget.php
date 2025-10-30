<?php

namespace App\Filament\Resources\DataReportsResource\Widgets;

use App\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockStatsWidget extends BaseWidget
{    
    protected ?string $heading = 'Inventory Overview';

    protected function getStats(): array
    {

        //product table with variantproduct table
        $products = Product::with('variants')->get();

        // Calculate totals
        $totalproducts = $products->count();
        $lowstockproducts = $products->filter(fn($prd) => $prd->total_stock_quantity <= 5)->count();
        $goodstockproducts = $products->filter(fn($prd) => $prd->total_stock_quantity > 5)->count();
        $totalstock = $products->sum(fn($prd) => $prd->total_stock_quantity);
        $outofstock = $products->filter(fn($prd) => $prd->total_stock_quantity === 0)->count();

        // Total inventory amount/value
        $totalValue = $products->sum(function ($prd) {
            return $prd->variants->sum(fn($v) => $v->stock_quantity * $prd->price);
        });

        // convertion sa peso pati num
        $valueFormatted = '₱' . number_format($totalValue, 2);
        $stockFormatted = number_format($totalstock);

        return [
        Stat::make('Total Products', $totalproducts)
        ->description('All active products in catalog')
        ->icon('heroicon-o-cube') 
        ->color('info'),

        Stat::make('Low Stock', $lowstockproducts)
            ->description('Products running low')
            ->icon('heroicon-o-exclamation-triangle') 
            ->color('warning'),

        Stat::make('Good Stock', $goodstockproducts)
            ->description('Products with healthy stock')
            ->icon('heroicon-o-check-circle') 
            ->color('success'),

        Stat::make('Total Stock Quantity', $stockFormatted)
            ->description('Sum of all variant quantities')
            ->icon('heroicon-o-archive-box') 
            ->color('primary'),

        Stat::make('Total Inventory Value', $valueFormatted)
            ->description('Approx. total value based on unit price')
            ->icon('heroicon-o-currency-dollar') // 💰
            ->color('success'),

        Stat::make('Out of Stock', $outofstock)
            ->description('Products that need restocking')
            ->icon('heroicon-o-x-circle') 
            ->color('danger'),
  
            
        ];
        
    }
  
}
