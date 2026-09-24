<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\User;

beforeEach(function () {
    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());
});

test('visitors see the public pages in the main menu', function () {
    $this->get(route('charte.show'))
        ->assertOk()
        ->assertSee('data-test="nav-home"', false)
        ->assertSee('data-test="nav-contact"', false)
        ->assertSee('data-test="nav-charte"', false)
        ->assertSee('data-test="nav-anonymous-access"', false)
        ->assertDontSee('data-test="nav-dossiers"', false)
        ->assertDontSee('data-test="nav-administration"', false);
});

test('members only see their tools in the main menu', function () {
    $this->actingAs(User::factory()->parent()->create())
        ->get(route('charte.show'))
        ->assertOk()
        ->assertSee('data-test="nav-dossiers"', false)
        ->assertSee('data-test="nav-meetings"', false)
        ->assertSeeInOrder(['data-test="nav-password"', 'data-test="nav-attestation-benevolat"', 'data-test="logout-button"'], false)
        ->assertSee('data-test="user-menu-attestation-alert"', false)
        ->assertDontSee('data-test="nav-home"', false)
        ->assertDontSee('data-test="nav-contact"', false)
        ->assertDontSee('data-test="nav-charte"', false)
        ->assertDontSee('data-test="nav-anonymous-access"', false)
        ->assertDontSee('data-test="nav-administration"', false);
});

test('administrators get the administration pages grouped in a dropdown', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('charte.show'))
        ->assertOk()
        ->assertSee('data-test="nav-dossiers"', false)
        ->assertSeeInOrder(['data-test="nav-administration"', 'data-test="nav-membres"', 'data-test="nav-cartouches"', 'data-test="nav-attestations"'], false)
        ->assertDontSee('data-test="nav-home"', false);
});
