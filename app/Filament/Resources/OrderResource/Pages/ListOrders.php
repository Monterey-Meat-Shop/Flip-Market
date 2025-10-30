<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Shipping;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All')
                ->badge(Order::count())
                ->badgeColor('gray'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'pending'))
                ->badge(Order::where('order_status', 'pending')->count())
                ->badgeColor('warning'),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'processing'))
                ->badge(Order::where('order_status', 'processing')->count())
                ->badgeColor('info'),

            'in_transit' => Tab::make('In Transit')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereHas('shipping', function ($subQuery) {
                        $subQuery->where('shipping_status', 'in_transit');
                    });
                })
                ->badge(Shipping::where('shipping_status', 'in_transit')->count())
                ->badgeColor('info'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereHas('shipping', function ($subQuery) {
                        $subQuery->where('shipping_status', 'delivered');
                    });
                })
                ->badge(Shipping::where('shipping_status', 'delivered')->count())
                ->badgeColor('success'),

            'returned' => Tab::make('Returned')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'returned'))
                ->badge(Order::where('order_status', 'returned')->count())
                ->badgeColor('danger'),

            'archived' => Tab::make('Archived')
                ->badge(Order::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badgeColor('danger'),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }
}
