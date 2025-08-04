<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // query for active
            'active' => Tab::make('Active Brands')
                ->badge(Brand::query()->where('is_active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            // query for inactive
            'inactive' => Tab::make('Inactive Brands')
                ->badge(Brand::query()->where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),

            'archived' => Tab::make('Archived Brands')
                // FIX: Use the Brand model directly for the badge count.
                // ->badge(Brand::onlyTrashed()->count())
                // ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}