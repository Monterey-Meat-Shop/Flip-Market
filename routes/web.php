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
use App\Http\Controllers\ReturnController;

use App\Livewire\ProductDetailPage;
use App\Livewire\ProductPage;
use App\Livewire\ReturnPage;
use App\Livewire\ReturnConfirmationPage;

use App\Livewire\Auth\ResetPassword; // ✅ make sure this import line exists
use Illuminate\Support\Facades\Mail; // test email route


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

// Forgot Password (🔹 add this line)
use App\Livewire\Auth\ForgotPassword; // make sure this import exists at the top
Route::get('/forgot-password', ForgotPassword::class)->name('forgot-password');

// Reset Password (🔹 add this line)
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');

// //test email route
// Route::get('/test-email', function () {
//     try {
//         Mail::raw('This is a test email from Flip Market (local setup working fine).', function ($message) {
//             $message->to('your_email@gmail.com') // ← put your real Gmail address here
//                     ->subject('Flip Market Test Email');
//         });
//         return '✅ Test email sent successfully! Check your inbox.';
//     } catch (\Exception $e) {
//         return '❌ Failed: ' . $e->getMessage();
//     }
// });

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/checkout', CheckoutPage::class)->name('checkout');
    Route::get('/my-orders', MyOrderPage::class)->name('my.orders');
    Route::get('/my-account', MyAccountPage::class)->name('my.account');

    // Return routes - NEW/UPDATED
    Route::get('/return/{orderId}', ReturnPage::class)->name('return.page');
    Route::post('/returns/{order}/submit', [ReturnController::class, 'submit'])->name('returns.submit');
    Route::get('/returns/confirmation', [ReturnController::class, 'confirmation'])->name('returns.confirmation');
    // Route::get('/returns/confirmation', [ReturnController::class, 'confirmation'])->name('returns.confirmation');
    Route::get('/returns/{returnId}', [ReturnController::class, 'show'])->name('returns.show');
    
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