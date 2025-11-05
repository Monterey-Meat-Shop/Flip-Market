<?php

use App\Http\Controllers\NotificationController;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\CartPage;
use App\Livewire\Landingpage;
use App\Livewire\CheckoutPage;
use App\Livewire\MyOrderPage;
use App\Livewire\MyAccountPage;
use App\Livewire\OrderDetailPage;
use App\Livewire\FavoritePage;

use App\Http\Controllers\CartController;
use App\Http\Controllers\ReturnController as ReturnCtrl;
use App\Livewire\NotificationPage;
use App\Livewire\ProductDetailPage;
use App\Livewire\ProductPage;
use App\Livewire\ReturnPage;

use App\Http\Controllers\ReturnController;
use App\Http\Controllers\OrderPrintController;

use App\Livewire\Auth\ResetPassword; // ✅ make sure this import line exists
use Illuminate\Support\Facades\Mail; // test email route

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use Illuminate\Auth\Middleware\EnsureEmailIsVerified; // ✅ Added

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified']) // ✅ Verified users only
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth'])->group(function () {
    Route::get('filament/transactions/{order}/print', [OrderPrintController::class, 'print'])
        ->name('filament.transactions.print');
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

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/checkout', CheckoutPage::class)->name('checkout');
    Route::get('/my-orders', MyOrderPage::class)->name('my.orders');
    Route::get('/my-account', MyAccountPage::class)->name('my.account');
    Route::get('/favorites', FavoritePage::class)->name('favorites');

    Route::get('/notifications', NotificationPage::class)->name('notifications.page');

    // Backend endpoints (for marking read, etc.)
    Route::post('/notifications/{id}/read', [NotificationPage::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationPage::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Return routes 
    Route::get('/return/{orderId}', ReturnPage::class)->middleware('verified')->name('return.page');
    Route::post('/returns/{order}/submit', [ReturnController::class, 'submit'])->name('returns.submit');
    Route::get('/returns/confirmation', [ReturnController::class, 'confirmation'])->name('returns.confirmation');
    Route::get('/returns/{returnId}', [ReturnController::class, 'show'])->name('returns.show');

    // Order details
    Route::get('/orders/{orderId}', OrderDetailPage::class)->middleware('verified')->name('orders.show');

    Route::get('/logout', function () {
        Auth::logout(); 
        return redirect('/');
    });
});

Route::get('/returns', [ReturnCtrl::class, 'index'])->name('returns.index');

