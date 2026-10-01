<?php

use App\Livewire\Membres;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Features;
use Livewire\Livewire;

test('a successful login is recorded with its date', function () {
    $this->travelTo(Carbon::parse('2026-10-01 09:15'));
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    expect($user->logins()->sole()->logged_in_at->format('Y-m-d H:i'))->toBe('2026-10-01 09:15');
});

test('a failed login is not recorded', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password']);

    expect($user->logins()->count())->toBe(0);
});

test('a login waiting for its two factor challenge is not recorded yet', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));

    expect($user->logins()->count())->toBe(0);
});

test('administrators see the number of logins and the first and last login of each member', function () {
    $this->actingAs(User::factory()->admin()->create());

    $regular = User::factory()->parent()->create(['name' => 'Alice Martin']);
    $regular->logins()->createMany([
        ['logged_in_at' => '2026-09-03 08:30'],
        ['logged_in_at' => '2026-09-28 19:05'],
        ['logged_in_at' => '2026-09-15 12:00'],
    ]);
    User::factory()->professeur()->create(['name' => 'Bruno Petit']);

    Livewire::test(Membres::class)
        ->assertSeeInOrder(['Alice Martin', '3', '03/09/2026 08:30', '28/09/2026 19:05', 'Bruno Petit', '0', 'Jamais', 'Jamais']);
});
