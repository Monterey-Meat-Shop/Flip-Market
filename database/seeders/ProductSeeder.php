<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brands = DB::table('brands')->pluck('brandID', 'name');
        $categories = DB::table('categories')->pluck('categoryID', 'name');

        // 20 sample products
        $products = [
            ['name' => 'SB APRIL PINK', 'brand' => 'Nike', 'category' => 'Womens', 'price' => 1200.00],
            ['name' => 'SB DUNK BLACK', 'brand' => 'Nike', 'category' => 'Mens', 'price' => 1200.00],
            ['name' => 'GTCUT 3 MUSTARD', 'brand' => 'Nike', 'category' => 'Sneaker', 'price' => 1500.00],
            ['name' => 'AIR MAX TN BLACK', 'brand' => 'Nike', 'category' => 'Running', 'price' => 1300.00],
            ['name' => 'ADIDAS SAMBA WHITE', 'brand' => 'Adidas', 'category' => 'Sneaker', 'price' => 1200.00],
            ['name' => 'VANS OLD SKOOL', 'brand' => 'Vans', 'category' => 'Skaters', 'price' => 1250.00],
            ['name' => 'CONVERSE CHUCK TAYLOR', 'brand' => 'Converse', 'category' => 'High Tops', 'price' => 1270.00],
            ['name' => 'JORDAN 1 LOW RED', 'brand' => 'Jordan', 'category' => 'Basketball', 'price' => 1400.00],
            ['name' => 'PUMA SPEEDCAT RED', 'brand' => 'Puma', 'category' => 'Sneaker', 'price' => 1250.00],
            ['name' => 'PUMA SPEEDCAT BLACK', 'brand' => 'Puma', 'category' => 'Sneaker', 'price' => 1250.00],
            ['name' => 'NEW BALANCE 530 WHITE', 'brand' => 'New Balance', 'category' => 'Running', 'price' => 1250.00],
            
        ];

        foreach ($products as $productData) {
            // Random date between May 1 and July 31 of this year
            $createdAt = Carbon::createFromTimestamp(rand(
                Carbon::create(now()->year, 10, 1)->timestamp,
                Carbon::create(now()->year, 7, 31)->timestamp
            ));

            $productId = DB::table('products')->insertGetId([
                'categoryID' => $categories[$productData['category']],
                'brandID' => $brands[$productData['brand']],
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['name'] . ' description.',
                'price' => $productData['price'],
                'status' => 'in_stock',
                'is_active' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $sizes = [48, 46, 45];
            foreach ($sizes as $size) {
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'size' => $size,
                    'stock_quantity' => 5,
                    'colorway' => 'Default',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
