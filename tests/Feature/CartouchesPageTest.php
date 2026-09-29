<?php

use App\Enums\CartoucheIcone;
use App\Livewire\Cartouches;
use App\Models\Cartouche;
use App\Models\User;
use Livewire\Livewire;

test('the cartouches page redirects guests without the access cookie', function () {
    $this->get(route('cartouches.show'))
        ->assertRedirect(route('access.show'));
});

test('the cartouches page is accessible and lists cartouches with a valid access cookie', function () {
    $cartouche = Cartouche::factory()->create([
        'icone' => CartoucheIcone::Ecoute,
        'titre' => 'Écoute confidentielle',
    ]);

    $member = User::factory()->parent()->create();

    $response = $this->actingAs($member)
        ->withCookies(withAccessCookie())
        ->get(route('cartouches.show'));

    $response->assertOk();
    $response->assertSeeText('Nos cartouches');
    $response->assertSeeText($cartouche->titre);
});

test('a cartouche can be created', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test(Cartouches::class)
        ->call('create')
        ->set('icone', CartoucheIcone::Bienveillance->value)
        ->set('titre', 'Bienveillance')
        ->set('description', 'Une description de test.')
        ->call('save')
        ->assertSet('showModal', false);

    expect(Cartouche::where('titre', 'Bienveillance')->first()->icone)->toBe(CartoucheIcone::Bienveillance);
});

test('a new cartouche is proposed after the last one', function () {
    $this->withCookies(withAccessCookie());

    Cartouche::factory()->create(['position' => 4]);

    Livewire::test(Cartouches::class)
        ->call('create')
        ->assertSet('position', 5);
});

test('a cartouche position must be a positive integer', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test(Cartouches::class)
        ->call('create')
        ->set('position', 0)
        ->call('save')
        ->assertHasErrors(['position' => 'min']);
});

test('a cartouche cannot use an icon outside the proposed list', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test(Cartouches::class)
        ->call('create')
        ->set('icone', '🌱')
        ->set('titre', 'Bienveillance')
        ->set('description', 'Une description de test.')
        ->call('save')
        ->assertHasErrors(['icone']);
});

test('a cartouche cannot be created without required fields', function () {
    $this->withCookies(withAccessCookie());

    Livewire::test(Cartouches::class)
        ->call('create')
        ->set('titre', '')
        ->call('save')
        ->assertHasErrors(['icone', 'titre', 'description']);
});

test('a cartouche can be updated', function () {
    $this->withCookies(withAccessCookie());

    $cartouche = Cartouche::factory()->create(['titre' => 'Ancien titre']);

    Livewire::test(Cartouches::class)
        ->call('edit', $cartouche->id)
        ->set('titre', 'Nouveau titre')
        ->set('position', 7)
        ->call('save')
        ->assertSet('showModal', false);

    expect($cartouche->fresh())
        ->titre->toBe('Nouveau titre')
        ->position->toBe(7);
});

test('a cartouche can be deleted', function () {
    $this->withCookies(withAccessCookie());

    $cartouche = Cartouche::factory()->create();

    Livewire::test(Cartouches::class)
        ->call('delete', $cartouche->id);

    expect(Cartouche::find($cartouche->id))->toBeNull();
});
