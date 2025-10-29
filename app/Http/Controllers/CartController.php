<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Add a product to the cart
     */
    public function addToCart(Request $request, $productId)
    {
        $customer = Auth::user()->customer;
        $variantId = $request->input('variant_id');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $product = Product::with('discounts')->findOrFail($productId);
        $variant = ProductVariant::findOrFail($variantId);

        if ($variant->stock_quantity < $quantity) {
            return response()->json(['error' => 'Not enough stock available.'], 400);
        }

        $activeDiscount = $this->getActiveDiscount($product);
        $unitPrice = $this->calculateDiscountedPrice($variant->price ?? $product->price, $activeDiscount);

        DB::transaction(function () use ($customer, $product, $variant, $unitPrice, $quantity, $activeDiscount) {
            // Reduce stock
            $variant->decrement('stock_quantity', $quantity);

            // Add or update cart item
            CartItem::updateOrCreate(
                [
                    'customerID' => $customer->customerID,
                    'productID' => $product->productID,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'quantity' => DB::raw("quantity + {$quantity}"),
                    'unit_price' => $unitPrice,
                    'sub_total' => DB::raw("({$unitPrice}) * quantity"),
                ]
            );
        });

        return response()->json(['message' => 'Item added to cart successfully.']);
    }

    /**
     * Remove a product from the cart
     */
    public function removeFromCart($itemId)
    {
        $cartItem = CartItem::with('variant')->findOrFail($itemId);

        DB::transaction(function () use ($cartItem) {
            // Return the stock to product variant
            if ($cartItem->variant) {
                $cartItem->variant->increment('stock_quantity', $cartItem->quantity);
            }

            $cartItem->delete();
        });

        return response()->json(['message' => 'Item removed successfully.']);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request, $itemId)
    {
        $newQuantity = (int) $request->input('quantity', 1);

        $cartItem = CartItem::with(['product.discounts', 'variant'])->find($itemId);

        if (!$cartItem) {
            return response()->json(['error' => 'Cart item not found.'], 404);
        }

        if ($newQuantity <= 0) {
            $this->removeFromCart($itemId);
            return response()->json(['message' => 'Item removed successfully.']);
        }

        try {
            DB::transaction(function () use ($cartItem, $newQuantity) {
                $variant = $cartItem->variant->fresh();

                if (!$variant) {
                    throw new \Exception('Product variant not found.');
                }

                $currentStock = $variant->stock_quantity;
                $totalStock = $currentStock + $cartItem->quantity;

                if ($newQuantity > $totalStock) {
                    throw new \Exception("Only {$totalStock} items are available in stock.");
                }

                $stockDifference = $newQuantity - $cartItem->quantity;

                if ($stockDifference > 0) {
                    $variant->decrement('stock_quantity', $stockDifference);
                } elseif ($stockDifference < 0) {
                    $variant->increment('stock_quantity', abs($stockDifference));
                }

                $unitPrice = $cartItem->variant->price ?? $cartItem->product->price;
                $activeDiscount = $this->getActiveDiscount($cartItem->product);
                $discountedPrice = $this->calculateDiscountedPrice($unitPrice, $activeDiscount);

                $cartItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $discountedPrice,
                    'sub_total' => $discountedPrice * $newQuantity,
                ]);
            });

            return response()->json(['message' => 'Cart updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Helper: Get active discount
     */
    private function getActiveDiscount($product)
    {
        return $product->discounts()
            ->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Helper: Calculate discounted price
     */
    private function calculateDiscountedPrice($price, $discount)
    {
        if (!$discount) return $price;

        if (strtolower($discount->discount_type) === 'percentage') {
            return $price - ($price * ($discount->discount_value / 100));
        }

        return max(0, $price - $discount->discount_value);
    }
}
