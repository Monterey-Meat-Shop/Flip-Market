<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('MutateFormDataBeforeCreate called:', $data);

        $finalAmount = $data['final_amount'] ?? ($data['total_amount'] ?? 0);
        $data['final_amount'] = $finalAmount;

        $data['order_status'] = $data['order_status'] ?? 'pending';
        $data['shipping_method'] = $data['shipping_method'] ?? 'pickup';
        $data['shipping_status'] = $data['shipping_status'] ?? 'pending';

        Log::info('Final amount and defaults set:', [
            'final_amount' => $data['final_amount'],
            'order_status' => $data['order_status'],
            'shipping_method' => $data['shipping_method'],
            'shipping_status' => $data['shipping_status'],
        ]);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $formData = $this->form->getState();

        // --- Payment ---
        $paymentData = [
            'payment_methodID' => $formData['payment_methodID'] ?? null,
            'amount'           => $formData['amount'] ?? $record->final_amount,
            'reference_number' => $formData['reference_number'] ?? null,
            'status'           => $formData['status'] ?? 'unpaid',
        ];

        if (empty($paymentData['payment_methodID'])) {
            throw new \Exception('Payment method is required.');
        }

        $payment = $record->payment()->create($paymentData);

        // --- Shipping (OrderResource only) ---
        $record->shipping()->create([
            'shipping_method' => $formData['shipping_method'] ?? 'pickup',
            'shipping_status' => $formData['shipping_status'] ?? 'pending',
        ]);

        // --- Update order status (persisted) ---
        $record->update([
            'order_status' => $formData['order_status'] ?? 'pending',
        ]);

        // refresh so we get latest attributes (and relationships are present)
        $record->refresh();

        // --- Deduct for OrderResource: if order_status is 'pending' and not yet deducted ---
        if ($record->order_status === 'pending' && ! ($record->stock_deducted ?? false)) {
            $record->deductStockForPendingOrder();
            Log::info("Stock deducted for Order {$record->orderID} (pending flow).");
        }

        // Optional fallback: if payment was paid immediately, ensure deduction (only if not already done)
        if (in_array(strtolower($payment->status), ['paid', 'verified', 'completed']) && ! ($record->stock_deducted ?? false)) {
            $record->deductStockForTransaction();
            Log::info("Stock deducted for Order {$record->orderID} (payment was paid).");
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
            ->title('Order created')
            ->body('The order has been created successfully.');
    }
}
