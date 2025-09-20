<?php

use App\Livewire\CartPage;
use App\Livewire\CategoriesPage;
use App\Livewire\BrandPage;
use App\Livewire\Landingpage;

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
Route::get('/', Landingpage::class);
Route::get('/products', ProductPage::class);
Route::get('/categories', CategoriesPage::class);
Route::get('/brands', BrandPage::class);
Route::get('/cart', CartPage::class);
Route::get('/products/{product}', ProductDetailPage::class);




