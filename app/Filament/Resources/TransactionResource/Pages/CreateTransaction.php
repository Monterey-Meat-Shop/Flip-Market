<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $formData = $this->form->getState();

        Log::info('Transaction afterCreate - Form Data:', $formData);

        $payment = $record->payment;
        
        if ($payment) {
            // Update payment status to paid for transactions
            $payment->update([
                'status' => 'paid'
            ]);
            
            Log::info("Payment updated for Transaction {$record->orderID}", [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'method' => $payment->paymentMethod->method_name ?? 'Unknown'
            ]);
        } else {
            Log::error("No payment found for Transaction {$record->orderID}");
            throw new \Exception('Payment creation failed.');
        }

        // --- Update order status ---
        $record->update([
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $record->refresh();

        // --- Deduct stock (this method is idempotent via stock_deducted) ---
        $record->deductStockForTransaction();
        
        Log::info("Stock deducted for Transaction {$record->orderID}");
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Transaction created')
            ->body('The transaction has been created successfully, and stock has been deducted.');
    }
}