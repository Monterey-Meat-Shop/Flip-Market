<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockStatusNotification extends Notification
{
    use Queueable;

    public $product;
    public $status;

    public function __construct(Product $product, string $status)
    {
        $this->product = $product;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->status === 'low_stock' ? 'Low Stock Alert' : 'Out of Stock Alert',
            'body' => $this->status === 'low_stock'
                ? "Product '{$this->product->name}' is low in stock ({$this->product->total_stock_quantity} left)."
                : "Product '{$this->product->name}' is out of stock!",
            'product_id' => $this->product->productID,
            'name' => $this->product->name,
            'status' => $this->status,
            'stock_quantity' => $this->product->total_stock_quantity,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}