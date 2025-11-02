  <?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{

    public function created(OrderItem $orderItem): void
    {
        
    }
    
    public function updated(OrderItem $orderItem): void
    {
        
    }

    public function deleted(OrderItem $orderItem): void
    {
        // Only restore stock if the order had already deducted stock
        if ($orderItem->order && $orderItem->order->stock_deducted) {
            $this->restoreStock($orderItem, $orderItem->quantity);
        }
    }

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