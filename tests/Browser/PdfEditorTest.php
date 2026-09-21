<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('lets a logged-in parent open the pdf editor and see it boot in a real browser', function () {
    // Home::mount() queries User::role('parent'), which requires the role to exist
    // (unlike the whereHas('roles', ...) query it replaced) — see MembresPageTest.php.
    Role::firstOrCreate(['name' => 'parent']);

    $parent = User::factory()->parent()->create();

    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());

    $page = visit(route('login'));

    $page->fill('email', $parent->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page->navigate(route('home'));

    $page->click('@nav-pdf-editor')
        ->assertPathIs('/editeur-pdf')
        ->assertSee('Éditeur PDF')
        ->assertSee('Glissez-déposez un fichier PDF ici');
});
