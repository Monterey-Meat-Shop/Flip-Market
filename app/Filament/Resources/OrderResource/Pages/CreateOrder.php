<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Payment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        $order = $this->getRecord();
        
        // Retrieve the payment data from the form state.
        $paymentData = $this->form->getState();
        
        // Create the payment record with the correct amount from the newly created order.
        Payment::create([
            'orderID' => $order->orderID,
            'amount' => $order->total_amount,
            'payment_methodID' => $paymentData['payment_methodID'],
            'reference_number' => $paymentData['reference_number'],
            'status' => $paymentData['status'],
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
