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
    $response->assertSeeText('Nos valeurs fondamentales');
    $response->assertSeeText('Confidentialité');
    $response->assertSeeText('Neutralité');
    $response->assertSeeText('Nos engagements envers vous');
    $response->assertSeeText('Composition du Cercle');
});
