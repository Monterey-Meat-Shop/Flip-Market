<?php

namespace App\Filament\Resources\StockOrderResource\Pages;

use App\Filament\Resources\StockOrderResource;
use App\Models\StockOrder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateStockOrder extends CreateRecord
{
    protected static string $resource = StockOrderResource::class;

    protected array $formData = [];

    protected function handleRecordCreation(array $data): Model
    {
        $orderItems = $data['stockItems'] ?? [];
        if (empty($orderItems)) {
            throw new \Exception('Please add at least one product.');
        }

        $this->formData = $data;

        $stockOrder = StockOrder::create([
            'supplier_name' => $data['supplier_name'],
            'purchase_order_number' => $data['purchase_order_number'] ?? null,
            'estimated_delivery_date' => $data['estimated_delivery_date'],
            'notes' => $data['notes'] ?? null,
            'received_quantity' => 0,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        foreach ($orderItems as $item) {
            $stockOrder->stockItems()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'stock_quantity' => $item['stock_quantity'],
                'stock_quantity_received' => 0,
            ]);
        }

        return $stockOrder;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $stockItems = $this->formData['stockItems'] ?? [];
        $count = count($stockItems);

        return Notification::make()
            ->title('Stock Orders Created')
            ->body("Successfully created {$count} stock order item(s).")
            ->success();
    }
}
