<?php

use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\CartPage;
use App\Livewire\Landingpage;
use App\Livewire\CheckoutPage;
use App\Livewire\MyOrderPage;
use App\Livewire\MyAccountPage;
use App\Livewire\OrderDetailPage;

use App\Http\Controllers\CartController;

use App\Livewire\ProductDetailPage;
use App\Livewire\ProductPage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

// Public routes
Route::get('/', Landingpage::class)->name('landingpage');
Route::get('/products', ProductPage::class)->name('products');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/product/{productId}', ProductDetailPage::class)->name('product.detail');

// Login/Register
Route::get('/login', LoginPage::class)->name('login');
Route::get('/register', RegisterPage::class)->name('register');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/checkout', CheckoutPage::class)->name('checkout');
    Route::get('/my-orders', MyOrderPage::class)->name('my.orders');
    Route::get('/my-account', MyAccountPage::class)->name('my.account');
    
    // Order details - pass orderId as parameter
    Route::get('/orders/{orderId}', OrderDetailPage::class)->name('orders.show');
    
    // Cart actions
    Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{itemId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{itemId}', [CartController::class, 'updateQuantity'])->name('cart.update');
    
    Route::get('/logout', function () {
        Auth::logout(); 
        return redirect('/');
    });
});