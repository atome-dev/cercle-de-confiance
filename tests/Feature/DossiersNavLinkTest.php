<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // The "parent" role always exists in production (seeded by RoleSeeder on every
    // deploy) even before any parent user is registered — Home::mount() uses
    // User::role() to list members, which requires the role to exist.
    Role::firstOrCreate(['name' => 'parent']);
});

function visitHomeAs(?User $user)
{
    $request = test()->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());

    if ($user) {
        $request->actingAs($user);
    }

    return $request->get(route('home'));
}

test('the "Dossiers" nav link is visible to a parent', function () {
    $user = User::factory()->parent()->create();

    visitHomeAs($user)->assertSeeText('Dossiers');
});

test('the "Dossiers" nav link is visible to a professeur', function () {
    $user = User::factory()->professeur()->create();

    visitHomeAs($user)->assertSeeText('Dossiers');
});

test('the "Dossiers" nav link is visible to an administrateur', function () {
    $user = User::factory()->admin()->create();

    visitHomeAs($user)->assertSeeText('Dossiers');
});

test('the "Dossiers" nav link is not visible to a guest', function () {
    visitHomeAs(null)->assertDontSeeText('Dossiers');
});

test('the "Dossiers" nav link is not visible to an authenticated user with no relevant role', function () {
    $user = User::factory()->create();

    visitHomeAs($user)->assertDontSeeText('Dossiers');
});
