<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Livewire\Membres;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function withAccessCookie(): array
{
    return ['access_granted' => EnsureAccessCodeIsValid::expectedCookieValue()];
}

beforeEach(function () {
    // The "parent" role always exists in production (seeded by RoleSeeder on every
    // deploy) even before any parent user is registered — User::role() requires the
    // role to exist, unlike the whereHas('roles', ...) query it replaced.
    Role::firstOrCreate(['name' => 'parent']);
});

test('the membres page redirects guests without the access cookie', function () {
    $this->get(route('membres.show'))
        ->assertRedirect(route('access.show'));
});

test('the membres page is accessible and lists members with a valid access cookie', function () {
    $membre = User::factory()->parent()->create(['name' => 'Nicolas Chauvet']);
    $membre->forceFill(['membre_titre' => "Parent d'élève"])->save();

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->withCookies(withAccessCookie())
        ->get(route('membres.show'));

    $response->assertOk();
    $response->assertSeeText('Nos membres');
    $response->assertSeeText($membre->name);
    $response->assertSeeText($membre->membre_titre);
});

test('a member can be created', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->withCookies(withAccessCookie());

    Livewire::actingAs($admin)->test(Membres::class)
        ->call('create')
        ->set('name', 'Alice Martin')
        ->set('membre_titre', 'Professeure')
        ->set('membre_role', 'professeur')
        ->set('email', 'alice.martin@example.com')
        ->call('save')
        ->assertSet('showModal', false);

    $created = User::where('name', 'Alice Martin')->first();
    expect($created)->not->toBeNull()
        ->and($created->membre_titre)->toBe('Professeure')
        ->and($created->membre_role)->toBe('professeur')
        ->and($created->hasRole('professeur'))->toBeTrue()
        ->and($created->password)->not->toBeNull();
});

test('a member cannot be created without required fields', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Membres::class)
        ->call('create')
        ->set('name', '')
        ->set('email', '')
        ->call('save')
        ->assertHasErrors(['name', 'membre_titre', 'email']);
});

test('a member can be updated', function () {
    $admin = User::factory()->admin()->create();
    $membre = User::factory()->parent()->create(['name' => 'Ancien Nom']);
    $membre->forceFill(['membre_titre' => "Parent d'élève", 'membre_role' => 'parent'])->save();

    Livewire::actingAs($admin)->test(Membres::class)
        ->call('edit', $membre->id)
        ->set('name', 'Nouveau Nom')
        ->call('save')
        ->assertSet('showModal', false);

    expect($membre->fresh()->name)->toBe('Nouveau Nom');
});

test('updating a member syncs their role', function () {
    $admin = User::factory()->admin()->create();
    $membre = User::factory()->parent()->create();
    $membre->forceFill(['membre_titre' => "Parent d'élève", 'membre_role' => 'parent'])->save();

    Livewire::actingAs($admin)->test(Membres::class)
        ->call('edit', $membre->id)
        ->set('membre_role', 'professeur')
        ->call('save');

    $membre->refresh();
    expect($membre->membre_role)->toBe('professeur')
        ->and($membre->hasRole('professeur'))->toBeTrue()
        ->and($membre->hasRole('parent'))->toBeFalse();
});

test('a member can be deleted', function () {
    $admin = User::factory()->admin()->create();
    $membre = User::factory()->parent()->create();

    Livewire::actingAs($admin)->test(Membres::class)
        ->call('delete', $membre->id);

    expect(User::find($membre->id))->toBeNull();
});
