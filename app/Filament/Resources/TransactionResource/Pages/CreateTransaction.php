<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Transaction MutateFormDataBeforeCreate called:', $data);

        // Calculate final amount from order items (which already have discounted prices)
        $finalAmount = collect($data['orderItems'] ?? [])->sum('sub_total');
        $data['final_amount'] = $finalAmount;
        $data['total_amount'] = $finalAmount; // Keep both fields in sync

        // Set default statuses
        $data['order_status'] = $data['order_status'] ?? 'completed';

        Log::info('Transaction final amount and defaults set:', [
            'total_amount' => $data['total_amount'],
            'final_amount' => $data['final_amount'],
            'order_status' => $data['order_status'],
        ]);

        // Log discount information for each order item
        foreach ($data['orderItems'] ?? [] as $index => $item) {
            if (!empty($item['discount_name'])) {
                Log::info("Transaction Item {$index} has discount applied:", [
                    'product_id' => $item['productID'] ?? 'N/A',
                    'discount_name' => $item['discount_name'],
                    'original_price' => $item['original_price'] ?? 'N/A',
                    'final_price' => $item['unit_price'] ?? 'N/A',
                    'discount_amount' => $item['discount_amount'] ?? 'N/A',
                ]);
            }
        }

        // Ensure payment data is properly structured
        if (isset($data['payment'])) {
            $data['payment']['status'] = $data['payment']['status'] ?? 'paid';
        }

        return $data;
    }

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
        ]);

        $record->refresh();

        // Log final transaction totals with discount information
        Log::info("Transaction {$record->orderID} created with totals:", [
            'total_amount' => $record->total_amount,
            'final_amount' => $record->final_amount,
            'payment_amount' => $payment->amount,
            'has_discounted_items' => $record->orderItems()->whereNotNull('discount_name')->exists(),
        ]);

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
        // Get discount information for the notification
        $discountedItemsCount = $this->record->orderItems()
            ->whereNotNull('discount_name')
            ->count();
        
        $message = 'The transaction has been created successfully, and stock has been deducted.';
        if ($discountedItemsCount > 0) {
            $message .= " {$discountedItemsCount} item(s) had discounts applied.";
        }

        return Notification::make()
            ->success()
            ->title('Transaction created')
            ->body($message);
    }
}