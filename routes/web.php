<?php

use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\CartPage;
use App\Livewire\CategoriesPage;
use App\Livewire\BrandPage;
use App\Livewire\Landingpage;
use App\Livewire\CheckoutPage;

use App\Livewire\ProductDetailPage;
use App\Livewire\ProductPage;

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Models\Product;

use function Pest\Laravel\get;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Route::get('/test-archived-products', function() {
//     $archivedProducts = Product::onlyTrashed()->get();
//     dd($archivedProducts->toArray());
// });


require __DIR__.'/auth.php';


// Customer Side product page
Route::get('/', Landingpage::class)->name('landingpage');
Route::get('/products', ProductPage::class)->name('products');
Route::get('/categories', CategoriesPage::class)->name('categories');
Route::get('/brands', BrandPage::class)->name('brands');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/product/{productId}', ProductDetailPage::class)->name('product.detail');

// lOGIN
Route::get('/login', LoginPage::class)->name('login');
Route::get('/register', RegisterPage::class)->name('register');

// Auth
Route::get('/login', LoginPage::class)->name('login');
Route::get('/register', RegisterPage::class)->name('register');

// Cart Actions (must be logged in)
Route::middleware(['auth'])->group(function () {
    Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{itemId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{itemId}', [CartController::class, 'updateQuantity'])->name('cart.update');
});

// Customer Orders Page
// Route::middleware(['auth'])->group(function () {
//     Route::get('/orders', [OrderController::class, 'index'])->name('orders');
// });




