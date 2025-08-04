<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

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
        return [
            // query for total available
            'all' => Tab::make('All Available')
                 ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                 ->badge(Product::withoutTrashed()->count()),

            // query for total in stock
            'in_stock' => Tab::make('In Stock')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'in_stock'))
                ->badge(Product::withoutTrashed()->where('status', 'in_stock')->count())
                ->badgeColor('success'),
            
            // query for total pre-order
            'pre_order' => Tab::make('Pre-order')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'pre_order'))
                ->badge(Product::withoutTrashed()->where('status', 'pre_order')->count())
                ->badgeColor('info'),
            
            // query for total low stock
            'low_stock' => Tab::make('Low Stock')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('stock_quantity', '<=', 10))
                ->badge(Product::withoutTrashed()->where('stock_quantity', '<=', 10)->count())
                ->badgeColor('warning'),

            // query for total out of stock    
            'out_of_stock' => Tab::make('Out of Stock')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'out_of_stock'))
                ->badge(Product::withoutTrashed()->where('status', 'out_of_stock')->count())
                ->badgeColor('danger'),
             
            // query for total archived
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(Product::onlyTrashed()->count())
                ->badgeColor('secondary'),
        ];
    }
}