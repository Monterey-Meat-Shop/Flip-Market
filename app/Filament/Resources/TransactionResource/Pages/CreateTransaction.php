<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function afterCreate(): void
    {
        $order = $this->record;
        $paymentData = $this->data;
        
        // Find the correct payment method ID based on the method name from the form
        $paymentMethod = PaymentMethod::where('method_name', $paymentData['payment_method'])->first();

        // Create the new Payment record with the correctly mapped data
        // We check if a payment method was found to prevent errors
        if ($paymentMethod) {
            Payment::create([
                'orderID' => $order->orderID,
                'amount' => $order->total_amount,
                'payment_methodID' => $paymentMethod->payment_methodID,
                'reference_number' => $paymentData['reference_number'] ?? null,
                'status' => $paymentData['payment_status'],
            ]);
        }

        // Now we will also update the product stock quantities after a successful order is created.
        foreach ($order->orderItems as $item) {
            $product = $item->product; // Access the product relationship directly

            if ($product) {
                // Decrease the stock quantity and save it to the database immediately.
                $product->stock_quantity -= $item->quantity;
                $product->save();
            }
        }
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
            ->body('The transaction has been created successfully.');
    }
}
