<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        if ($orderItem->productVariant) {
            // Deduct from product variant stock
            $orderItem->productVariant->decrement('stock_quantity', $orderItem->quantity);
        } elseif ($orderItem->product) {
            // Deduct from main product stock if no variant
            $orderItem->product->decrement('stock_quantity', $orderItem->quantity);
        }
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        //
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        if ($orderItem->productVariant) {
            // Restore stock if item is removed
            $orderItem->productVariant->increment('stock_quantity', $orderItem->quantity);
        } elseif ($orderItem->product) {
            $orderItem->product->increment('stock_quantity', $orderItem->quantity);
        }
    }

    /**
     * Handle the OrderItem "restored" event.
     */
    public function restored(OrderItem $orderItem): void
    {
        //
    }

    /**
     * Handle the OrderItem "force deleted" event.
     */
    public function forceDeleted(OrderItem $orderItem): void
    {
        //
    }
}
