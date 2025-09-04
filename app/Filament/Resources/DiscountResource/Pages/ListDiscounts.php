<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array 
    {
        return [
            'all' => Tab::make('All Available')
                ->badge(static::getModel()::where('is_active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            'fixed' => Tab::make('Fixed Discounts')
                ->badge(static::getModel()::where('discount_type', 'Fixed')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('discount_type', 'Fixed'))
                ->badgeColor('success'),

            'percentage' => Tab::make('Percentage Discounts')
                ->badge(static::getModel()::where('discount_type', 'Percentage')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('discount_type', 'Percentage'))
                ->badgeColor('success'),

            'inactive' => Tab::make('Inactive Discounts')
                ->badge(static::getModel()::where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badgeColor('danger'),
            
            'trashed' => Tab::make('Archived Discounts')
                ->badge(static::getModel()::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badgeColor('gray'),
        ];

    }
}
