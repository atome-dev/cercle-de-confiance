<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;

test('the charte page redirects guests without the access cookie', function () {
    $this->get(route('charte.show'))
        ->assertRedirect(route('access.show'));
});

test('the charte page is accessible and renders its content with a valid access cookie', function () {
    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('charte.show'));

    $response->assertOk();
    $response->assertSeeText('Notre Charte');
    $response->assertSeeText('Raison d’être');
    $response->assertSeeText('Résultats attendus du cercle');
    $response->assertSeeText('Rôle et périmètre');
    $response->assertSeeText('Missions principales');
    $response->assertSeeText('Missions complémentaires');
    $response->assertSeeText('Les limites du Cercle de Confiance');
});
