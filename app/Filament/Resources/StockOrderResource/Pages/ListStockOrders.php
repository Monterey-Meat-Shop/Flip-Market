<?php

namespace App\Filament\Resources\StockOrderResource\Pages;

use App\Filament\Resources\StockOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListStockOrders extends ListRecords
{
    protected static string $resource = StockOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // Check for overdue orders and send notification
        $overdueCount = \App\Models\StockOrder::query()
            ->whereIn('status', ['pending', 'partial'])
            ->where('estimated_delivery_date', '<', now())
            ->count();

        if ($overdueCount > 0) {
            Notification::make()
                ->title('Overdue Stock Orders')
                ->body("You have {$overdueCount} overdue stock order(s). Please review them.")
                ->warning()
                ->persistent()
                ->send();
        }

        // Check for approaching deliveries
        $approachingCount = \App\Models\StockOrder::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('estimated_delivery_date', [now(), now()->addDays(3)])
            ->count();

        if ($approachingCount > 0) {
            Notification::make()
                ->title('Upcoming Deliveries')
                ->body("You have {$approachingCount} stock order(s) arriving within 3 days.")
                ->info()
                ->send();
        }
    }
}
