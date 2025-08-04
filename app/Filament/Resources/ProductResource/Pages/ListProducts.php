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

    // public function getTabs(): array
    // {
        
    //     return [
    //         null => Tab::make('All'),

    //         'in_stock' => Tab::make('In Stock')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_stock'))
    //             ->badge(Product::query()->where('status', 'in_stock')->count())
    //             ->badgeColor('success'),

    //         'pre_order' => Tab::make('Pre-order')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pre_order'))
    //             ->badge(Product::query()->where('status', 'pre_order')->count())
    //             ->badgeColor('info'),

    //         'low_stock' => Tab::make('Low Stock')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_stock')->where('stock_quantity', '<=', 10))
    //             ->badge(Product::query()->where('status', 'in_stock')->where('stock_quantity', '<=', 5)->count())
    //             ->badgeColor('warning'),

    //         'out_of_stock' => Tab::make('Out of Stock')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'out_of_stock'))
    //             ->badge(Product::query()->where('status', 'out_of_stock')->count())
    //             ->badgeColor('danger'),

    //         'inactive' => Tab::make('Inactive')
    //         ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
    //         ->badge(Product::query()->where('is_active', false)->count())
    //         ->badgeColor('secondary'),

    //         'archived' => Tab::make('Archived')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
    //             ->badge(Product::onlyTrashed()->count())
    //             ->badgeColor('secondary'),
    //     ];
    // }
}