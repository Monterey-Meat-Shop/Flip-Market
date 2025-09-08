<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Payment;
use App\Models\Shipping;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    
    protected function afterCreate(): void
    {
        $record = $this->record;
        $formData = $this->form->getState();

        // Create the Payment record
        Payment::create([
            'orderID' => $record->orderID,
            'payment_methodID' => Arr::get($formData, 'payment_methodID'),
            'amount' => Arr::get($formData, 'downpayment', 0),
            'reference_number' => Arr::get($formData, 'reference_number'),
            'status' => Arr::get($formData, 'status', 'unpaid'),
        ]);

        // Create the Shipping record
        Shipping::create([
            'orderID' => $record->orderID,
            'shipping_method' => Arr::get($formData, 'shipping_method'),
            'shipping_status' => Arr::get($formData, 'shipping_status'),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Order created')
            ->body('The order has been created successfully.');
    }
}
