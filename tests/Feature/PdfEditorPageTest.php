<?php

use App\Models\User;

test('the pdf editor page redirects guests without the access cookie', function () {
    $this->get(route('pdf-editor.show'))
        ->assertRedirect(route('access.show'));
});

test('a user with none of administrateur, parent or professeur gets a 403 on the pdf editor page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pdf-editor.show'))
        ->assertForbidden();
});

test('the pdf editor page is accessible to a parent with a valid access cookie', function () {
    $member = User::factory()->parent()->create();

    $response = $this->actingAs($member)
        ->withCookies(withAccessCookie())
        ->get(route('pdf-editor.show'));

    $response->assertOk();
    $response->assertSeeText('Éditeur PDF');
    $response->assertSee('id="pdf-canvas"', false);
});
