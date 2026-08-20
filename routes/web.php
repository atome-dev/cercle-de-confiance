<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home')->middleware('access.code');

Route::view('/charte', 'pages::charte')->name('charte.show')->middleware('access.code');

Route::livewire('/membres', 'pages::membres')->name('membres.show')->middleware('access.code');

Route::livewire('/cartouches', 'pages::cartouches')->name('cartouches.show')->middleware('access.code');

Route::livewire('/acces', 'pages::access-gate')->name('access.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
