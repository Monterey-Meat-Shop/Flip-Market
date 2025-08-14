<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Payment;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    // Use a custom method to update data before creating the record.
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['customerID'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        // The payment fields are on the root level of the form data, not in a nested 'payment' key.
        $paymentData = $this->data;
        
        // Create the payment record with the correct amount from the newly created order.
        Payment::create([
            'orderID' => $order->orderID,
            'amount' => $order->total_amount,
            'payment_methodID' => $paymentData['payment_methodID'],
            // Fix: Use the null coalescing operator to safely set the reference number
            'reference_number' => $paymentData['reference_number'] ?? null, 
            'status' => $paymentData['status'],
        ]);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
