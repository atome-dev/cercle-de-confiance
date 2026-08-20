<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\Cartouche;
use App\Models\Membre;

test('the homepage renders the hero, the members and the cartouches', function () {
    $membre = Membre::factory()->create([
        'nom' => 'Nicolas Chauvet',
        'titre' => "Parent d'élève",
        'role' => 'parent',
    ]);

    $cartouche = Cartouche::factory()->create([
        'icone' => '🤝',
        'titre' => 'Écoute confidentielle',
    ]);

    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertSeeText("Un espace d'écoute, de confiance et de bienveillance");

    $response->assertSeeText($membre->nom);
    $response->assertSeeText($membre->titre);

    $response->assertSeeText('Que faisons-nous ?');
    $response->assertSeeText($cartouche->titre);
});
