<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $archivedQuery = fn ($query) => $query->onlyTrashed();//for archived
        return [

            'all' => Tab::make('All Users')
            ->badge(User::count())
            ->modifyQueryUsing(fn (Builder $query) => $query),

            'admin' => Tab::make('Admin')
            ->badge(User::query()->where('is_active', true)->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->count())
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))),

            'manager' => Tab::make('Manager')
            ->badge(User::query()->where('is_active', true)->whereHas('roles', fn ($query) => $query->where('name', 'manager'))->count())
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('name', 'manager'))),

            'cashier' => Tab::make('Cashier')
                ->badge(User::query()->where('is_active', true)->whereHas('roles', fn ($query) => $query->where('name', 'cashier'))->count())
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('name', 'cashier'))),

            'customer' => Tab::make('Customer')
                ->badge(User::query()->where('is_active', true)->whereHas('roles', fn ($query) => $query->where('name', 'customer'))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('name', 'customer'))),

            'archived' => Tab::make('Archived')
                ->badge(User::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),

        ];
    }
}
