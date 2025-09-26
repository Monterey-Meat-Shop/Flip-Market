<?php 

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\Discount;
use App\Models\Product;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected array $orderItemsData = [];

    /**
     * Turn a money-like input into integer cents.
     */
    private function toCents(string|float|int|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        
        // Handle string values that might have currency symbols
        if (is_string($value)) {
            $clean = preg_replace('/[^\d.-]/', '', $value);
        } else {
            $clean = (string) $value;
        }
        
        // Convert to float first, then to cents
        $floatValue = (float) $clean;
        return (int) round($floatValue * 100);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Full form data before processing:', $data);

        $this->orderItemsData = $data['orderItems'] ?? [];

        if (empty($this->orderItemsData)) {
            Notification::make()
                ->title('Cart Error')
                ->body('At least one product must be added to the cart.')
                ->danger()
                ->send();
            $this->halt();
        }

        $this->orderItemsData = collect($this->orderItemsData)
            ->filter(fn ($item) => !empty($item['productID']) && !empty($item['quantity']) && (int) $item['quantity'] > 0)
            ->values()
            ->toArray();

        if (empty($this->orderItemsData)) {
            Notification::make()
                ->title('Cart Error')
                ->body('No valid items found in cart.')
                ->danger()
                ->send();
            $this->halt();
        }

        Log::info('Valid order items found:', [
            'count' => count($this->orderItemsData),
            'items' => $this->orderItemsData,
        ]);

        // Calculate the final total from order items
        $calculatedTotal = 0;
        foreach ($this->orderItemsData as $item) {
            $calculatedTotal += ($item['sub_total'] ?? 0);
        }

        // Get the final amount from form data
        $finalAmount = $data['final_amount'] ?? $calculatedTotal;
        
        // Ensure we're working with the calculated total if form data is missing
        if (!$finalAmount || $finalAmount == 0) {
            $finalAmount = $calculatedTotal;
        }

        Log::info('Amount validation:', [
            'calculated_total' => $calculatedTotal,
            'form_final_amount' => $data['final_amount'] ?? 'not set',
            'payment_amount' => $data['payment']['amount'] ?? 'not set',
            'final_amount_used' => $finalAmount
        ]);

        // Get payment amount - use the calculated total if not provided
        $paymentAmount = $data['payment']['amount'] ?? $finalAmount;
        
        // Convert both to cents for comparison
        $finalTotalCents = $this->toCents($finalAmount);
        $paymentCents = $this->toCents($paymentAmount);

        Log::info('Cents comparison:', [
            'final_total_cents' => $finalTotalCents,
            'payment_cents' => $paymentCents,
        ]);

        // Allow small rounding differences (1 cent tolerance)
        if (abs($paymentCents - $finalTotalCents) > 1) {
            Notification::make()
                ->title('Payment Error')
                ->body(
                    'Amount must exactly match the final total. '
                    . 'Payment: ₱' . number_format($paymentCents / 100, 2)
                    . ' • Final Total: ₱' . number_format($finalTotalCents / 100, 2)
                )
                ->danger()
                ->send();

            $this->halt();
        }

        Notification::make()
            ->success()
            ->title('Payment Accepted')
            ->body('Amount received: ₱' . number_format($finalTotalCents / 100, 2) . '.')
            ->send();

        $finalAmountFloat = $finalTotalCents / 100;

        // Update the data with correct values
        $data['final_amount'] = $finalAmountFloat;
        $data['total_amount'] = $finalAmountFloat;
        $data['payment']['amount'] = $finalAmountFloat;

        // Remove orderItems from the main data as they'll be handled separately
        unset($data['orderItems']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        foreach ($this->orderItemsData as $item) {
            $product = Product::find($item['productID']);
            if ($product && !empty($item['discount_name'])) {
                $discount = Discount::where('name', $item['discount_name'])->first();
                if ($discount) {
                    $item['unit_price'] = $discount->getFinalPrice($product->price);
                    $item['sub_total'] = $item['unit_price'] * $item['quantity'];
                }
            }
            $this->record->orderItems()->create($item);
        }

        $payment = $record->payment;

        if ($payment) {
            $payment->update([
                'status' => 'paid',
                'amount' => $record->final_amount,
            ]);
        } else {
            throw new \Exception('Payment creation failed.');
        }

        $record->update([
            'order_status' => 'completed',
        ]);

        $record->refresh();

        try {
            $record->deductStockForTransaction();
        } catch (\Exception $e) {
            Log::error("Stock deduction failed for Transaction {$record->orderID}: " . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->record;
        $discountedCount = $record->orderItems()->whereNotNull('discount_name')->count();
        $totalItemsCount = $record->orderItems()->count();

        $message = "Transaction completed successfully! {$totalItemsCount} item(s) processed and stock has been deducted.";
        if ($discountedCount > 0) {
            $message .= " {$discountedCount} item(s) had discounts applied.";
        }

        return Notification::make()
            ->success()
            ->title('Transaction Created')
            ->body($message)
            ->duration(5000);
    }
}