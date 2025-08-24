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
        // The observer should not directly check the `stock_quantity` on the product model,
        // but rather on its related variants. The `ProductResource` form already handles
        // setting the status, so this observer logic may be redundant, but if it's needed
        // for other parts of the application, it should be updated to use the total stock.
        
        // This logic is now handled in the ProductResource form, but if you need a fallback,
        // you would check if the variants have changed, and then update the product status.
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
