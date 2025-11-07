<?php

namespace App\Filament\Resources\StockOrderResource\Pages;

use App\Filament\Resources\StockOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStockOrder extends EditRecord
{
    protected static string $resource = StockOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Stock Order Updated')
            ->body('The stock order has been updated successfully.')
            ->success()
            ->send();
    }

    // Prevent editing completed orders
    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->status === 'completed') {
            Notification::make()
                ->title('Cannot Edit Completed Order')
                ->body('This stock order has been completed and cannot be edited.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }
}
