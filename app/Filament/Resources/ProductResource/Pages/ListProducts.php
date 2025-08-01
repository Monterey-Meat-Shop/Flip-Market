<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product; // Import the Product model
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // A query for available products, using a more descriptive variable name
        $availableQuery = fn ($query) => $query->where('is_active', true)->where('stock_quantity', '>', 0);
        $lowStockQuery = fn ($query) => $query->where('is_active', true)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
        $outOfStockQuery = fn ($query) => $query->where('is_active', true)->where('stock_quantity', '=', 0);
        $archivedQuery = fn ($query) => $query->onlyTrashed();

        return [
            'available' => Tab::make('Products Available')
                ->modifyQueryUsing($availableQuery)
                ->badge(Product::query()->where('is_active', true)->where('stock_quantity', '>', 0)->count()),

            'low_stock' => Tab::make('Low Stock')
                ->modifyQueryUsing($lowStockQuery)
                ->badge(Product::query()->where('is_active', true)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count())
                ->badgeColor('warning'),

            'out_of_stock' => Tab::make('Out of Stock')
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', false))
                ->badge(Product::where('is_active', false)->count())
                ->badgeColor('danger'),

            // 'inactive' => Tab::make('Inactive')
            //      ->modifyQueryUsing(fn ($query) => $query->where('is_active', false))
            //      ->badge(Product::where('is_active', false)->count()),

            'archived' => Tab::make('Archived')
                ->badge(Product::onlyTrashed()->count())
                ->modifyQueryUsing($archivedQuery),
        ];
    }
}