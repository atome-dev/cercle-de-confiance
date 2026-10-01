<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\User;
use Carbon\CarbonImmutable;

it('paints a slot of the availability grid with the chosen mode', function () {
    $parent = User::factory()->parent()->create();
    $monday = CarbonImmutable::now()->addWeek()->startOfWeek(CarbonImmutable::MONDAY);
    $slot = $monday->addDays(2)->format('Y-m-d').' 18:00';

    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());

    $page = visit(route('login'));

    $page->fill('email', $parent->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page->navigate(route('meetings.index', ['onglet' => 'disponibilites', 'semaine' => $monday->format('Y-m-d')]))
        ->click('@brush-distanciel')
        ->click('[data-slot="'.$slot.'"]')
        ->assertAttribute('[data-slot="'.$slot.'"]', 'data-mode', 'distanciel')
        ->assertNoJavaScriptErrors();

    expect($parent->meetingAvailabilities()->sole()->mode->value)->toBe('distanciel');
});
