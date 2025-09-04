<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
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
                // This badge uses a direct Eloquent query.
                ->badge(Order::count()),
            
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'pending'))
                ->badge(Order::where('order_status', 'pending')->count())
                ->badgeColor('success'),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'processing'))
                ->badge(Order::where('order_status', 'processing')->count())
                ->badgeColor('gray'),

            'shipped' => Tab::make('Shipped')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'shipped'))
                ->badge(Order::where('order_status', 'shipped')->count())
                ->badgeColor('info'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'delivered'))
                ->badge(Order::where('order_status', 'delivered')->count())
                ->badgeColor('success'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'completed'))
                ->badge(Order::where('order_status', 'completed')->count())
                ->badgeColor('success'),

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
