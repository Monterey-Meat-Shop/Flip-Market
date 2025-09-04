<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Filament\Forms\Get;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getFormSchema(): array
    {
        // Re-use the schema from the main resource file.
        return $this->getResource()::form(
            $this->getForm()->getForm()
        )->getSchema();
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Add the customerID to the data. This is still necessary.
        $data['customerID'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        $paymentData = $this->data;
        
        Payment::create([
            'orderID' => $order->orderID,
            'amount' => $order->total_amount,
            'payment_methodID' => $paymentData['payment_methodID'],
            'reference_number' => $paymentData['reference_number'] ?? null, 
            'status' => $paymentData['status'],
        ]);

        foreach ($order->orderItems as $item) {
            $product = Product::find($item->productID);

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
            ->title('Order created')
            ->body('The order has been created successfully.');
    }
}
