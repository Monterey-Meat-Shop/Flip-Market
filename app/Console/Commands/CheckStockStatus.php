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

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $title = $this->status === 'low_stock' ? 'Low Stock Alert' : 'Out of Stock Alert';
        $body = $this->status === 'low_stock'
            ? "Product '{$this->product->name}' is low in stock ({$this->product->total_stock_quantity} left)."
            : "Product '{$this->product->name}' is out of stock!";

        return [
            'format' => 'filament',          // Important for the bell
            'title' => $title,
            'body' => $body,
            'icon' => 'heroicon-o-exclamation-triangle',
            'iconColor' => $this->status === 'low_stock' ? 'warning' : 'danger',
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'markAsRead',
                    'label' => 'Mark as read',
                    'close' => true,
                ],
            ],
            'product_id' => $this->product->productID,
            'name' => $this->product->name,
            'status' => $this->status,
            'stock_quantity' => $this->product->total_stock_quantity,
        ];
    }
}
