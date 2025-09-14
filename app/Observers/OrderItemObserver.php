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
        // DISABLED: Stock deduction is now handled in Filament pages only
        // $this->handleStockDeduction($orderItem);
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        // Only restore stock if the order had already deducted stock
        if ($orderItem->order && $orderItem->order->stock_deducted) {
            $this->restoreStock($orderItem, $orderItem->quantity);
        }
    }

    // Keep these private methods in case you need them later for manual adjustments
    
    /**
     * Centralized stock deduction logic.
     */
    private function handleStockDeduction(OrderItem $orderItem): void
    {
        if (!$orderItem->order) return;

        $order = $orderItem->order;

        // Only deduct if not already deducted
        if ($order->stock_deducted) return;

        // Deduct for Order module: order_status = pending
        if ($order->order_status === 'pending') {
            $this->restoreAndDeduct($orderItem);
            $order->update(['stock_deducted' => true]);
        }

        // Deduct for Transaction module: payment_status = paid
        if ($order->payment_status === 'paid') {
            $this->restoreAndDeduct($orderItem);
            $order->update(['stock_deducted' => true]);
        }
    }

    /**
     * Adjust stock difference for updated quantity.
     */
    private function handleStockAdjustment(OrderItem $orderItem, int $difference): void
    {
        if (!$orderItem->order) return;

        $order = $orderItem->order;

        // Only adjust if order has already deducted stock
        if (!$order->stock_deducted) return;

        // Only deduct/restore if order_status = pending OR payment_status = paid
        if (in_array($order->order_status, ['pending']) || $order->payment_status === 'paid') {
            if ($difference > 0) {
                $this->restoreAndDeduct($orderItem, $difference);
            } elseif ($difference < 0) {
                $this->restoreStock($orderItem, abs($difference));
            }
        }
    }

    /**
     * Deduct stock for the given item and quantity.
     */
    private function restoreAndDeduct(OrderItem $orderItem, ?int $quantity = null): void
    {
        $quantity = $quantity ?? $orderItem->quantity;

        if ($orderItem->productVariant) {
            $orderItem->productVariant->decrement('stock_quantity', $quantity);
            $this->refreshProductStatus($orderItem->productVariant->product);
        } elseif ($orderItem->product) {
            $orderItem->product->decrement('stock_quantity', $quantity);
            $this->refreshProductStatus($orderItem->product);
        }
    }

    /**
     * Restore stock for deleted or reduced quantity.
     */
    private function restoreStock(OrderItem $orderItem, int $quantity): void
    {
        if ($orderItem->productVariant) {
            $orderItem->productVariant->increment('stock_quantity', $quantity);
            $this->refreshProductStatus($orderItem->productVariant->product);
        } elseif ($orderItem->product) {
            $orderItem->product->increment('stock_quantity', $quantity);
            $this->refreshProductStatus($orderItem->product);
        }
    }

    /**
     * Refresh product status based on stock.
     */
    private function refreshProductStatus($product): void
    {
        $totalStock = (int) $product->variants()->sum('stock_quantity');

        if ($product->status !== 'pre_order') {
            if ($totalStock === 0) {
                $product->status = 'out_of_stock';
            } elseif ($totalStock <= 4) {
                $product->status = 'low_stock';
            } else {
                $product->status = 'in_stock';
            }
        }

        $product->is_active = ($product->status === 'pre_order') || ($totalStock > 0);
        $product->saveQuietly();
    }
}