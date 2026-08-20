<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;

it('lets a visitor navigate from the homepage to the charte page and back', function () {
    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());

    $page = visit('/');

    $page->assertSee("Un espace d'écoute, de confiance et de bienveillance")
        ->click('@nav-charte')
        ->assertPathIs('/charte')
        ->assertSee('Notre Charte')
        ->assertSee('Nos valeurs fondamentales');

    $page->script('window.scrollTo(0, document.body.scrollHeight)');

    $page->assertSee('Nous contacter')
        ->click('@nav-home')
        ->assertPathIs('/')
        ->assertSee('Qui sommes-nous ?');
});
