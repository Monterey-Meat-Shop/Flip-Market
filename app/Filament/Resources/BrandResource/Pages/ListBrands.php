<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
            'active' => Tab::make('Active Brands')
                ->badge(Brand::query()->where('is_active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badgeColor('success'),

            'inactive' => Tab::make('Inactive Brands')
                ->badge(Brand::query()->where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badgeColor('danger'),
                
            'archived' => Tab::make('Archived Brands')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->withoutGlobalScopes([SoftDeletingScope::class])->onlyTrashed()
                )
                ->badge(Brand::onlyTrashed()->count())
                ->badgeColor('gray'),
        ];
    }
}
