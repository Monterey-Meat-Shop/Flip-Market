<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Landingpage extends Component
{
   public function render()
{
    $categoryColors = [
        'Basketball' => 'bg-orange-500',
        'Running' => 'bg-green-500',
        'Sneaker' => 'bg-blue-500',
        'Skaters' => 'bg-purple-500',
        'High Tops' => 'bg-red-500',
    ];

    $brands = Brand::where('is_active', 1)->get();

    // Get top product per category with brand info
    $products = Product::select(
            'products.productID',
            'products.name',
            'products.price',
            'products.image_url',
            'products.categoryID',
            'categories.name as category_name',
            'brands.name as brand_name',
            DB::raw('SUM(order_items.quantity) as total_ordered')
        )
        ->join('order_items', 'order_items.productID', '=', 'products.productID')
        ->join('orders', 'orders.orderID', '=', 'order_items.orderID')
        ->join('categories', 'categories.categoryID', '=', 'products.categoryID')
        ->leftJoin('brands', 'brands.brandID', '=', 'products.brandID') // ✅ include brand
        ->where('orders.order_status', '!=', 'cancelled')
        ->groupBy(
            'products.productID',
            'categories.name',
            'brands.name',
            'products.name',
            'products.price',
            'products.image_url',
            'products.categoryID'
        )
        ->orderByDesc('total_ordered')
        ->get()
        ->groupBy('category_name')
        ->map(fn($group) => $group->first()); // ✅ only top 1 per category

    // Add badge color
    foreach ($products as $product) {
        $categoryName = $product->category_name ?? '';
        $product->badge_color = $categoryColors[$categoryName] ?? 'bg-gray-500';
    }

    return view('livewire.landingpage', compact('brands', 'products'));
}

}
