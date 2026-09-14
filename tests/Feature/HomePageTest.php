<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\Cartouche;
use App\Models\User;

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
