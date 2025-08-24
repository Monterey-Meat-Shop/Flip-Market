<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Find the related payment record based on the order ID.
        // We use the 'payment' relationship on the Order model for cleaner code.
        $payment = $this->record->payment;
        
        // If a payment record is found, add its data to the form.
        if ($payment) {
            $data['reference_number'] = $payment->reference_number;
            $data['payment_status'] = $payment->status; // Correctly maps to the 'status' column on the Payment model
        }
        
        return $data;
    }

    // This method handles the saving of the Payment data after the Order record is updated.
    protected function afterSave(): void
    {
        $data = $this->data; // Get the form data
        
        // Find the related payment record for this order
        $payment = $this->record->payment;
    
        // If a payment record exists, update it with the new data from the form.
        if ($payment) {
            $payment->reference_number = $data['reference_number'] ?? null;
            $payment->status = $data['payment_status'] ?? null;
            $payment->save();
        }
    }
}
