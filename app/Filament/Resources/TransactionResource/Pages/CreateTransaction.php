<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected array $orderItemsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
{
    Log::info('Full form data before processing:', $data);
    
    // Grab items from form state
    $this->orderItemsData = $data['orderItems'] ?? [];
    
    // Validate we have items
    if (empty($this->orderItemsData)) {
        throw new \Exception('At least one product must be added to the cart.');
    }
    
    // Clean and validate order items
    $this->orderItemsData = collect($this->orderItemsData)
        ->filter(function ($item) {
            return !empty($item['productID']) && 
                   !empty($item['quantity']) && 
                   $item['quantity'] > 0;
        })
        ->toArray();
    
    if (empty($this->orderItemsData)) {
        throw new \Exception('No valid items found in cart.');
    }
    
    Log::info('Valid order items found:', ['count' => count($this->orderItemsData), 'items' => $this->orderItemsData]);
    
    // Calculate totals from the items we have
    $finalAmount = collect($this->orderItemsData)->sum('sub_total');
    $data['final_amount'] = $finalAmount;
    $data['total_amount'] = $finalAmount;
    
    // Remove from main $data so it doesn't cause SQL error
    unset($data['orderItems']);

    return $data;
}

    protected function afterCreate(): void
    {
        $record = $this->record;
        $formData = $this->form->getState();

        // Save orderItems manually
        foreach ($this->orderItemsData as $item) {
            $this->record->orderItems()->create($item);
        }

        Log::info('Transaction afterCreate - Form Data:', [
            'orderID' => $record->orderID,
            'total_amount' => $record->total_amount,
            'final_amount' => $record->final_amount,
        ]);

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

        // Update order status
        $record->update([
            'order_status' => 'completed',
        ]);

        $record->refresh();

        // Log final transaction totals with discount information
        Log::info("Transaction {$record->orderID} created with totals:", [
            'total_amount' => $record->total_amount,
            'final_amount' => $record->final_amount,
            'payment_amount' => $payment->amount,
            'order_items_count' => $record->orderItems()->count(),
            'has_discounted_items' => $record->orderItems()->whereNotNull('discount_name')->exists(),
        ]);

        // Deduct stock (this method is idempotent via stock_deducted)
        try {
            $record->deductStockForTransaction();
            Log::info("Stock deducted for Transaction {$record->orderID}");
        } catch (\Exception $e) {
            Log::error("Stock deduction failed for Transaction {$record->orderID}: " . $e->getMessage());
            // Don't throw exception here to avoid rolling back the transaction
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->record;
        
        // Get discount information for the notification
        $discountedItemsCount = $record->orderItems()
            ->whereNotNull('discount_name')
            ->count();
        
        $totalItemsCount = $record->orderItems()->count();
        
        $message = "Transaction completed successfully! {$totalItemsCount} item(s) processed and stock has been deducted.";
        
        if ($discountedItemsCount > 0) {
            $message .= " {$discountedItemsCount} item(s) had discounts applied.";
        }

        return Notification::make()
            ->success()
            ->title('Transaction Created')
            ->body($message)
            ->duration(5000); // Show for 5 seconds
    }
}
