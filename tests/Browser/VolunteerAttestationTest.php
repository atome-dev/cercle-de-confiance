<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('automatically loads the blank template when reaching the page via a nav link click', function () {
    // Home::mount() queries User::role('parent'), which requires the role to exist
    // (unlike the whereHas('roles', ...) query it replaced) — see MembresPageTest.php.
    Role::firstOrCreate(['name' => 'parent']);

    $parent = User::factory()->parent()->create();

    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());

    $page = visit(route('login'));

    $page->fill('email', $parent->email)
        ->fill('password', 'password')
        ->click('@login-button');

    // Régression : la page de l'éditeur PDF charge un module ES (resources/js/pdf-editor.js)
    // qui ne s'exécute qu'une seule fois par chargement de page. En arrivant sur cette page
    // via une navigation SPA (wire:navigate) depuis une autre page, ce module ne se
    // réexécutait pas et le modèle ne se chargeait plus automatiquement — seul un vrai
    // rechargement fonctionnait. Le lien "Attestation" n'a donc plus wire:navigate (voir
    // resources/views/layouts/public.blade.php), et ce test clique dessus depuis une autre
    // page (au lieu de naviguer directement) pour vérifier qu'un vrai chargement de page a
    // bien lieu et que le modèle se charge malgré tout automatiquement.
    $page->navigate(route('home'))
        ->click('@user-menu-trigger')
        ->click('@nav-attestation-benevolat');

    // Preuve que le modèle vierge a été chargé automatiquement (pas de clic
    // "Ouvrir" nécessaire) : le bouton Enregistrer, désactivé tant qu'aucun PDF
    // n'est chargé, devient actif.
    $page->assertPathIs('/attestation-benevolat')
        ->assertSee('Attestation de bénévolat')
        ->assertButtonEnabled('#btn-save');

    // Le clic sur "Enregistrer" (upload via fetch()) n'est volontairement pas testé ici :
    // il fait deadlocker le serveur de test mono-process de pest-plugin-browser, même en
    // déclenchant le clic via script() plutôt que via click() (le contournement qui
    // fonctionne pour ThreadSharingTest.php). Le comportement de
    // App\Http\Controllers\VolunteerAttestationController::store() est couvert par
    // tests/Feature/VolunteerAttestationTest.php, qui l'appelle directement via le client
    // de test Laravel.
});
