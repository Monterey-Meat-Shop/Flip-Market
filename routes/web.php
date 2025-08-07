<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Models\Product;

Route::get('/', function () {
    return view('homepage');
})->name('home');

Route::get('/products/{id}', function ($id) {
    $product = App\Models\Product::findOrFail($id);
    return view(
        'pages.product-details',
        compact('product')
    );
});


require __DIR__ . '/auth.php';
