<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderAutoRejectedNotification extends Notification
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

        $orderUrl = url('/admin/resources/orders/' . $this->order->orderID);

        return [
            'format' => 'filament',
            'title' => 'Order Auto-Rejected',
            'body' => "Order #{$this->order->orderID} from {$customerName} was automatically rejected due to insufficient stock. Products have been restocked.",
            'icon' => 'heroicon-o-exclamation-triangle',
            'iconColor' => 'warning',
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'View Order',
                    'url' => $orderUrl,
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