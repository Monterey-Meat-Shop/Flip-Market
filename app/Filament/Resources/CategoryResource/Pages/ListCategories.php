<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;


class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

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
            'active' => Tab::make('Active Categories')
                ->badge(Category::query()->where('is_active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            // query for inactive
            'inactive' => Tab::make('Inactive Categories')
                ->badge(Category::query()->where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),

             'archived' => Tab::make('Archived Categories')
            //     ->badge(Category::onlyTrashed()->count())
            //     ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}