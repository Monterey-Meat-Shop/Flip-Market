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
            // The 'All' tab badge should not expect a $query variable.
            // This is the most robust way to get the total count.
            null => Tab::make('All')
                ->badge(fn (): int => Order::count()),
            
            // The other tabs have a query() method, so they can use the $query argument.
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'pending'))
                ->badge(fn ($query) => $query->count()),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'processing'))
                ->badge(fn ($query) => $query->count()),

            'shipped' => Tab::make('Shipped')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'shipped'))
                ->badge(fn ($query) => $query->count()),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'delivered'))
                ->badge(fn ($query) => $query->count()),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', 'completed'))
                ->badge(fn ($query) => $query->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }
}
