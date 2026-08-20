<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Models\Membre;
use Livewire\Livewire;

function withAccessCookie(): array
{
    return ['access_granted' => EnsureAccessCodeIsValid::expectedCookieValue()];
}

test('the membres page redirects guests without the access cookie', function () {
    $this->get(route('membres.show'))
        ->assertRedirect(route('access.show'));
});

test('the membres page is accessible and lists members with a valid access cookie', function () {
    $membre = Membre::factory()->create([
        'nom' => 'Nicolas Chauvet',
        'titre' => "Parent d'élève",
        'role' => 'parent',
    ]);

    $response = $this->withCookies(withAccessCookie())->get(route('membres.show'));

    $response->assertOk();
    $response->assertSeeText('Nos membres');
    $response->assertSeeText($membre->nom);
    $response->assertSeeText($membre->titre);
});

test('a member can be created', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test('pages::membres')
        ->call('create')
        ->set('nom', 'Alice Martin')
        ->set('titre', 'Professeure')
        ->set('role', 'professeur')
        ->set('courriel', 'alice.martin@example.com')
        ->call('save')
        ->assertSet('showModal', false);

    expect(Membre::where('nom', 'Alice Martin')->exists())->toBeTrue();
});

test('a member cannot be created without required fields', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test('pages::membres')
        ->call('create')
        ->set('nom', '')
        ->call('save')
        ->assertHasErrors(['nom', 'titre']);
});

test('a member can be updated', function () {
    $this->withCookies(withAccessCookie());

    $membre = Membre::factory()->create(['nom' => 'Ancien Nom']);

    Livewire::test('pages::membres')
        ->call('edit', $membre->id)
        ->set('nom', 'Nouveau Nom')
        ->call('save')
        ->assertSet('showModal', false);

    expect($membre->fresh()->nom)->toBe('Nouveau Nom');
});

test('a member can be deleted', function () {
    $this->withCookies(withAccessCookie());

    $membre = Membre::factory()->create();

    Livewire::test('pages::membres')
        ->call('delete', $membre->id);

    expect(Membre::find($membre->id))->toBeNull();
});
