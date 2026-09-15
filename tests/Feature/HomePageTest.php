<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\Cartouche;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('the homepage renders the hero, the members and the cartouches', function () {
    $membre = User::factory()->parent()->create(['name' => 'Nicolas Chauvet']);
    $membre->forceFill([
        'membre_role' => 'parent',
        'membre_titre' => "Parent d'élève",
    ])->save();

    $cartouche = Cartouche::factory()->create([
        'icone' => '🤝',
        'titre' => 'Écoute confidentielle',
    ]);

    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertSeeText("Un espace d'écoute, de confiance et de bienveillance");

    $response->assertSeeText($membre->name);
    $response->assertSeeText($membre->membre_titre);

    $response->assertSeeText('Que faisons-nous ?');
    $response->assertSeeText($cartouche->titre);
});

test('an authenticated member sees their own avatar and a logout action in the header', function () {
    $user = User::factory()->parent()->create(['name' => 'Nicolas Chauvet']);

    $response = $this->actingAs($user)
        ->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertSeeText($user->name);
    $response->assertSee(route('logout'));
});

test('a guest does not see a logout action in the header', function () {
    $this->seed(RoleSeeder::class);

    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertDontSee(route('logout'));
});

test('a guest sees a link to access their dossier with a tracking code', function () {
    $this->seed(RoleSeeder::class);

    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertSee(route('anonymous-access'));
});

test('an authenticated member does not see the anonymous dossier access link', function () {
    $user = User::factory()->parent()->create();

    $response = $this->actingAs($user)
        ->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertDontSee(route('anonymous-access'));
});

test('an authenticated member sees a link to change their password', function () {
    $user = User::factory()->parent()->create();

    $response = $this->actingAs($user)
        ->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertSee(route('security.edit'));
});

test('a guest does not see a link to change a password', function () {
    $this->seed(RoleSeeder::class);

    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertDontSee(route('security.edit'));
});
