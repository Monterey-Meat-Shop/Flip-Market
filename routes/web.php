<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('homepage');
})->name('home');

require __DIR__.'/auth.php';
