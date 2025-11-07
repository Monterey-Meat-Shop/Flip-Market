<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Customer;

class CashierSalesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Guest user exists
        $guestUser = User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest',
                'last_name' => '',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        // Ensure Guest customer exists
        $guestCustomer = Customer::firstOrCreate(
            ['user_id' => $guestUser->id],
            [
                'first_name' => 'Guest',
                'last_name' => '',
                'phone' => '',
            ]
        );

        $customerID = $guestCustomer->customerID ?? $guestCustomer->id;

        // Get products and variants
        $products = DB::table('products')->get();
        $variants = DB::table('product_variants')->get();

        // Generate 20 guest orders
        for ($i = 0; $i < 20; $i++) {

            // Random order date: June 1 – October 31
            $orderDate = Carbon::createFromTimestamp(rand(
                Carbon::create(now()->year, 6, 1)->timestamp,
                Carbon::create(now()->year, 10, 31)->timestamp
            ));

            // Randomly pick 1–3 products
            $orderProducts = $products->random(rand(1, 3));
            $totalAmount = 0;
            $orderItems = [];

            foreach ($orderProducts as $product) {
                // Get variants for the product
                $productVariants = $variants->where('product_id', $product->productID)->values();
                if ($productVariants->isEmpty()) continue;

                $variant = $productVariants->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $subTotal = $unitPrice * $quantity;
                $totalAmount += $subTotal;

                $orderItems[] = [
                    'orderID' => 0, // will update after order insert
                    'productID' => $product->productID,
                    'product_variant_id' => $variant->id,
                    'discountID' => null,
                    'size' => $variant->size,
                    'colorway' => $variant->colorway,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'original_price' => $unitPrice,
                    'discount_name' => null,
                    'discount_amount' => 0,
                    'sub_total' => $subTotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];
            }

            // Skip order if no valid products
            if (empty($orderItems)) continue;

            // Insert order
            $orderID = DB::table('orders')->insertGetId([
                'customerID' => $customerID,
                'discountID' => null,
                'order_date' => $orderDate,
                'total_amount' => $totalAmount,
                'final_amount' => $totalAmount,
                'order_status' => 'completed',   // matches ENUM
                'payment_status' => 'paid',      // matches ENUM
                'stock_deducted' => true,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Update orderID in order items
            foreach ($orderItems as &$item) {
                $item['orderID'] = $orderID;
            }

            // Insert order items
            DB::table('order_items')->insert($orderItems);

            // Insert payment
            DB::table('payments')->insert([
                'orderID' => $orderID,
                'payment_methodID' => 1, // Cash
                'amount' => $totalAmount,
                'reference_number' => Str::upper(Str::random(10)),
                'screenshot_path' => null,
                'status' => 'paid',
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
        }
    }
}
