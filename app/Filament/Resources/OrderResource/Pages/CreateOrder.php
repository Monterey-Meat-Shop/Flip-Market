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
        Log::info('Order items data before create:', $data['orderItems'] ?? []);
        Log::info('MutateFormDataBeforeCreate called:', $data);

        // Calculate final amount from order items (which already have discounted prices)
        $finalAmount = collect($data['orderItems'] ?? [])->sum('sub_total');
        $data['final_amount'] = $finalAmount;
        $data['total_amount'] = $finalAmount; // Keep both fields in sync

        $data['order_status'] = $data['order_status'] ?? 'pending';
        $data['shipping_method'] = $data['shipping_method'] ?? 'pickup';
        $data['shipping_status'] = $data['shipping_status'] ?? 'pending';

        Log::info('Final amount and defaults set:', [
            'total_amount' => $data['total_amount'],
            'final_amount' => $data['final_amount'],
            'order_status' => $data['order_status'],
            'shipping_method' => $data['shipping_method'],
            'shipping_status' => $data['shipping_status'],
        ]);

        // Log discount information for each order item
        foreach ($data['orderItems'] ?? [] as $index => $item) {
            if (!empty($item['discount_name'])) {
                Log::info("Order Item {$index} has discount applied:", [
                    'product_id' => $item['productID'] ?? 'N/A',
                    'discount_name' => $item['discount_name'],
                    'original_price' => $item['original_price'] ?? 'N/A',
                    'final_price' => $item['unit_price'] ?? 'N/A',
                    'discount_amount' => $item['discount_amount'] ?? 'N/A',
                ]);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $formData = $this->form->getState();

        // --- Payment ---
        $paymentData = [
            'payment_methodID' => $formData['payment_methodID'] ?? null,
            'amount'           => $formData['downpayment'] ?? $record->final_amount, // Use downpayment field
            'reference_number' => $formData['reference_number'] ?? null,
            'status'           => $formData['status'] ?? 'unpaid',
        ];

        if (empty($paymentData['payment_methodID'])) {
            throw new \Exception('Payment method is required.');
        }

        $payment = $record->payment()->create($paymentData);

        // --- Shipping (OrderResource only) ---
        $record->shipping()->create([
            'shipping_method' => $formData['shipping_method'] ?? 'jnt',
            'shipping_status' => $formData['shipping_status'] ?? 'processing',
        ]);

        // --- Update order status (persisted) ---
        $record->update([
            'order_status' => $formData['order_status'] ?? 'pending',
        ]);

        // refresh so we get latest attributes (and relationships are present)
        $record->refresh();

        // Log final order totals with discount information
        Log::info("Order {$record->orderID} created with totals:", [
            'total_amount' => $record->total_amount,
            'final_amount' => $record->final_amount,
            'payment_amount' => $payment->amount,
            'has_discounted_items' => $record->orderItems()->whereNotNull('discount_name')->exists(),
        ]);

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
        // Get discount information for the notification
        $discountedItemsCount = $this->record->orderItems()
            ->whereNotNull('discount_name')
            ->count();
        
        $message = 'The order has been created successfully.';
        if ($discountedItemsCount > 0) {
            $message .= " {$discountedItemsCount} item(s) had discounts applied.";
        }

        return Notification::make()
            ->success()
            ->title('Order created')
            ->body($message);
    }
}