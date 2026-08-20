<?php

use App\Models\Cartouche;
use Livewire\Livewire;

test('the cartouches page redirects guests without the access cookie', function () {
    $this->get(route('cartouches.show'))
        ->assertRedirect(route('access.show'));
});

test('the cartouches page is accessible and lists cartouches with a valid access cookie', function () {
    $cartouche = Cartouche::factory()->create([
        'icone' => '🤝',
        'titre' => 'Écoute confidentielle',
    ]);

    $response = $this->withCookies(withAccessCookie())->get(route('cartouches.show'));

    $response->assertOk();
    $response->assertSeeText('Nos cartouches');
    $response->assertSeeText($cartouche->titre);
});

test('a cartouche can be created', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test('pages::cartouches')
        ->call('create')
        ->set('icone', '🌱')
        ->set('titre', 'Bienveillance')
        ->set('description', 'Une description de test.')
        ->call('save')
        ->assertSet('showModal', false);

    expect(Cartouche::where('titre', 'Bienveillance')->exists())->toBeTrue();
});

test('a cartouche cannot be created without required fields', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test('pages::cartouches')
        ->call('create')
        ->set('titre', '')
        ->call('save')
        ->assertHasErrors(['icone', 'titre', 'description']);
});

test('a cartouche can be updated', function () {
    $this->withCookies(withAccessCookie());

    $cartouche = Cartouche::factory()->create(['titre' => 'Ancien titre']);

    Livewire::test('pages::cartouches')
        ->call('edit', $cartouche->id)
        ->set('titre', 'Nouveau titre')
        ->call('save')
        ->assertSet('showModal', false);

    expect($cartouche->fresh()->titre)->toBe('Nouveau titre');
});

test('a cartouche can be deleted', function () {
    $this->withCookies(withAccessCookie());

    $cartouche = Cartouche::factory()->create();

    Livewire::test('pages::cartouches')
        ->call('delete', $cartouche->id);

    expect(Cartouche::find($cartouche->id))->toBeNull();
});
