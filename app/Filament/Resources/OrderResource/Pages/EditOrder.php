<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
    
    // This method is called after the main Order record is updated.
    protected function afterSave(): void
    {
        $record = $this->record;
        $formData = $this->form->getState();

        // Update the existing Payment record
        if ($record->payment) {
            $record->payment->update([
                'amount' => Arr::get($formData, 'downpayment', 0),
                'status' => Arr::get($formData, 'status', 'unpaid'),
            ]);
        }

        // Update the existing Shipping record
        if ($record->shipping) {
            $record->shipping->update([
                'shipping_status' => Arr::get($formData, 'shipping_status'),
            ]);
        }
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Order updated')
            ->body('The order has been updated successfully.');
    }
}
