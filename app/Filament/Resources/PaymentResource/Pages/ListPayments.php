<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // This tab will show all active payment methods that have not been soft-deleted.
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)->withoutTrashed())
                ->badge(PaymentMethod::query()->where('is_active', true)->withoutTrashed()->count())
                ->badgeColor('success'),

            // This tab will show all inactive payment methods that have not been soft-deleted.
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)->withoutTrashed())
                ->badge(PaymentMethod::query()->where('is_active', false)->withoutTrashed()->count())
                ->badgeColor('danger'),

            // This tab will show all soft-deleted records.
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(PaymentMethod::query()->onlyTrashed()->count())
                ->badgeColor('gray'),
        ];
    }
}
