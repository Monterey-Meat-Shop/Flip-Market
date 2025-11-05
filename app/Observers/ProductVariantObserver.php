<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "updated" event.
     * Check pending orders when stock changes (e.g., from POS sales)
     */
    public function updated(ProductVariant $productVariant)
    {
        // Only proceed if stock quantity actually changed
        if (! $productVariant->isDirty('stock_quantity')) {
            return;
        }

        $oldStock = $productVariant->getOriginal('stock_quantity');
        $newStock = $productVariant->stock_quantity;

        Log::info("🔄 ProductVariant {$productVariant->id} stock changed: {$oldStock} → {$newStock}");

        // If stock decreased (likely due to POS transaction or manual update)
        if ($newStock < $oldStock) {
            $this->checkPendingOrdersForVariant($productVariant);
        }
    }

    /**
     * Check all pending orders that contain this variant
     */
    protected function checkPendingOrdersForVariant(ProductVariant $productVariant)
    {
        $pendingOrders = Order::where('order_status', 'pending')
            ->whereHas('orderItems', function ($query) use ($productVariant) {
                $query->where('product_variant_id', $productVariant->id);
            })
            ->with(['orderItems.productVariant', 'orderItems.product'])
            ->get();

        foreach ($pendingOrders as $order) {
            $orderItem = $order->orderItems->firstWhere('product_variant_id', $productVariant->id);

            if ($orderItem && $orderItem->quantity > $productVariant->stock_quantity) {
                Log::warning("⚠️ Order {$order->orderID} has insufficient stock for variant {$productVariant->id} (needed: {$orderItem->quantity}, available: {$productVariant->stock_quantity})");

                // Auto reject AND restock the products only if stock was deducted
                if ($order->stock_deducted) {
                    $order->restockProducts();
                }

                // Cancel the order safely
                $order->update([
                    'order_status' => 'cancelled',
                ]);

                Log::info("Order {$order->orderID} auto-rejected and restocked due to insufficient stock.");
            }
        }
    }
}
