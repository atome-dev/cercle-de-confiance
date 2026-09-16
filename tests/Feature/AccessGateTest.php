<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Livewire\AccessGate;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Access codes are always configured in uppercase in practice — AccessGate
    // uppercases the submitted code before comparing against config('access.code').
    config(['access.code' => 'SECRET-CODE']);
    Cache::flush();

    // The "parent" role always exists in production (seeded by RoleSeeder on every
    // deploy) even before any parent user is registered — Home::mount() uses
    // User::role() to list members, which requires the role to exist.
    Role::firstOrCreate(['name' => 'parent']);
});

test('a public page redirects guests without the access cookie', function () {
    $this->get(route('home'))
        ->assertRedirect(route('access.show'));
});

test('submitting the correct code via the livewire component grants access', function () {
    $response = Livewire::test(AccessGate::class)
        ->set('code', 'secret-code')
        ->call('attempt');

    $response->assertHasNoErrors();
    $response->assertRedirect(route('home'));

    expect(Cookie::hasQueued('access_granted'))->toBeTrue()
        ->and(Cookie::queued('access_granted')->getValue())
        ->toBe(EnsureAccessCodeIsValid::expectedCookieValue());
});

test('submitting the wrong code shows an error and grants no access', function () {
    $response = Livewire::test(AccessGate::class)
        ->set('code', 'wrong-code')
        ->call('attempt');

    $response->assertHasErrors(['code']);
    $response->assertNoRedirect();

    expect(Cookie::hasQueued('access_granted'))->toBeFalse();
});

test('a request with a valid access cookie reaches the public page directly', function () {
    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'))
        ->assertOk();
});

test('a request with an invalid or forged access cookie is redirected to the gate', function () {
    $this->withCookie('access_granted', 'forged-value')
        ->get(route('home'))
        ->assertRedirect(route('access.show'));
});

test('an authenticated user bypasses the access gate without a cookie', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk();
});

test('the access code form is rate limited after too many failed attempts', function () {
    foreach (range(1, 6) as $attempt) {
        Livewire::test(AccessGate::class)
            ->set('code', 'wrong-code')
            ->call('attempt')
            ->assertHasErrors(['code']);
    }

    $response = Livewire::test(AccessGate::class)
        ->set('code', 'wrong-code')
        ->call('attempt');

    $response->assertHasErrors(['code']);
    $response->assertSee('Trop de tentatives');

    expect(Cookie::hasQueued('access_granted'))->toBeFalse();
});
