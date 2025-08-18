<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // We only want to run this logic if the stock_quantity has actually changed.
        if ($product->isDirty('stock_quantity')) {
            $stock = $product->stock_quantity;
            $newStatus = null;
            $isActive = $product->is_active;

            if ($stock === 0) {
                $newStatus = 'out_of_stock';
                $isActive = false;
            } elseif ($stock <= 4) {
                $newStatus = 'low_stock';
            } else {
                $newStatus = 'in_stock';
            }

            // Only update the status if it has changed from the old value.
            if ($product->status !== $newStatus || $product->is_active !== $isActive) {
                // To avoid an infinite loop where saving the model re-triggers the observer,
                // we'll temporarily remove the event dispatcher.
                $product->status = $newStatus;
                $product->is_active = $isActive;
                $product->withoutEvents(function () use ($product) {
                    $product->save();
                });
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
