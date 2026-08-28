<?php

use App\Http\Controllers\UserRoleController;
use App\Livewire\AnonymousAccess;
use App\Livewire\ContactForm;
use App\Livewire\ThreadShow;
use App\Livewire\ThreadsList;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->middleware('access.code')
    ->name('home');

Route::view('/charte', 'pages::charte')->middleware('access.code')
    ->name('charte.show');

Route::livewire('/acces', 'pages::access-gate')
    ->name('access.show');

Route::livewire('/contact', ContactForm::class)
    ->name('contact.show');
Route::get('/mon-dossier', AnonymousAccess::class)->name('anonymous-access');
Route::get('/dossiers/{thread}', ThreadShow::class)->name('threads.show');
Route::middleware(['auth', 'verified'])->group(function () {

    // Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware(['auth'])->get('/two-factor-setup', function () {
        return view('pages.auth.two-factor-setup');
    })->name('two-factor.setup');

    Route::middleware(['auth', 'role:administrateur|membre'])->group(function () {
        Route::livewire('/cartouches', 'pages::cartouches')->middleware('access.code')
            ->name('cartouches.show');

        Route::get('/dossiers', ThreadsList::class)->name('threads.index');
    });

    Route::middleware(['auth', 'role:administrateur', 'ensure2fa'])->group(function () {

        Route::livewire('/membres', 'pages::membres')->middleware('access.code')
            ->name('membres.show');
        Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])
            ->name('users.roles.update');
    });

});

require __DIR__.'/settings.php';
