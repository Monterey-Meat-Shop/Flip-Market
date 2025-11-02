<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $customerName = 'Unknown Customer';
        
        if ($this->order->customer) {
            $firstName = $this->order->customer->first_name ?? '';
            $lastName = $this->order->customer->last_name ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // This is the exact format Filament expects for database notifications
        return [
            'format' => 'filament',
            'title' => 'New Order Received',
            'body' => "Order #{$this->order->orderID} from {$customerName} - ₱" . number_format($this->order->final_amount, 2),
            'icon' => 'heroicon-o-shopping-bag',
            'iconColor' => 'success',
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'View Order',
                    'url' => url('/admin/resources/orders/' . $this->order->orderID),
                    'close' => false,
                ],
                [
                    'name' => 'markAsRead',
                    'label' => 'Mark as read',
                    'close' => true,
                ],
            ],
        ];
    }
}