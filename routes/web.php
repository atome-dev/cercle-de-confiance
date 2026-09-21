<?php

use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\VolunteerAttestationController;
use App\Livewire\AccessGate;
use App\Livewire\AnonymousAccess;
use App\Livewire\Cartouches;
use App\Livewire\ContactForm;
use App\Livewire\Home;
use App\Livewire\Membres;
use App\Livewire\PdfEditor;
use App\Livewire\ThreadShow;
use App\Livewire\ThreadsList;
use App\Livewire\VolunteerAttestation;
use App\Livewire\VolunteerAttestationsList;
use Illuminate\Support\Facades\Route;

Route::livewire('/acces', AccessGate::class)
    ->name('access.show');

// Pas de middleware access.code : une notice de confidentialité doit rester
// consultable sans barrière, y compris avant de saisir le code de l'établissement.
Route::view('/rgpd', 'pages::rgpd')
    ->name('rgpd.show');

Route::middleware('access.code')->group(function () {

    Route::livewire('/', Home::class)
        ->name('home');

    Route::view('/charte', 'pages::charte')
        ->name('charte.show');

    Route::livewire('/contact', ContactForm::class)
        ->name('contact.show');
    Route::livewire('/mon-dossier', AnonymousAccess::class)->name('anonymous-access');
    Route::livewire('/dossiers/{thread}', ThreadShow::class)->name('threads.show');
    Route::middleware(['auth', 'verified'])->group(function () {

        Route::middleware(['auth'])->get('/two-factor-setup', function () {
            return view('pages.auth.two-factor-setup');
        })->name('two-factor.setup');

        Route::middleware(['auth', 'role:administrateur|parent|professeur'])->group(function () {
            Route::livewire('/cartouches', Cartouches::class)->middleware('access.code')
                ->name('cartouches.show');

            Route::livewire('/dossiers', ThreadsList::class)->name('threads.index');

            Route::livewire('/editeur-pdf', PdfEditor::class)->middleware('access.code')
                ->name('pdf-editor.show');

            Route::livewire('/attestation-benevolat', VolunteerAttestation::class)->middleware('access.code')
                ->name('attestation-benevolat.show');
            Route::get('/attestation-benevolat/modele', [VolunteerAttestationController::class, 'template'])
                ->name('attestation-benevolat.template');
            Route::post('/attestation-benevolat', [VolunteerAttestationController::class, 'store'])
                ->name('attestation-benevolat.store');
            Route::get('/attestation-benevolat/telecharger', [VolunteerAttestationController::class, 'downloadMine'])
                ->name('attestation-benevolat.download');
        });

        Route::middleware(['auth', 'role:administrateur', 'ensure2fa'])->group(function () {

            Route::livewire('/membres', Membres::class)->middleware('access.code')
                ->name('membres.show');
            Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])
                ->name('users.roles.update');

            Route::livewire('/attestations-benevolat', VolunteerAttestationsList::class)->middleware('access.code')
                ->name('attestations-benevolat.index');
            Route::get('/attestations-benevolat/{attestation}/telecharger', [VolunteerAttestationController::class, 'download'])
                ->name('attestations-benevolat.download');
        });

    });
});

require __DIR__.'/settings.php';
