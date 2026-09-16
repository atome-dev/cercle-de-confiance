<?php

use App\Actions\CreateThreadWithMessage;
use App\Livewire\ThreadsList;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // The "parent" and "administrateur" roles always exist in production (seeded by
    // RoleSeeder on every deploy) even before any user holds them — User::role()
    // requires the role to exist, unlike the whereHas('roles', ...) query it replaced.
    Role::firstOrCreate(['name' => 'parent']);
    Role::firstOrCreate(['name' => 'administrateur']);
});

test('creating a thread encrypts the sender name and email at rest', function () {
    app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = Thread::sole();

    expect($thread->sender_name)->not->toBe('Jean Dupont')
        ->and($thread->sender_name)->not->toContain('Jean')
        ->and($thread->sender_email)->not->toBe('jean.dupont@example.com')
        ->and($thread->sender_email)->not->toContain('jean.dupont');
});

test('the sender name and email are readable by the app without any tracking code', function () {
    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    expect($result['thread']->decryptedSenderName())->toBe('Jean Dupont')
        ->and($result['thread']->decryptedSenderEmail())->toBe('jean.dupont@example.com');
});

test('an empty sender name is shown as "Anonyme" in the dossier list', function () {
    $parent = User::factory()->parent()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: '',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    expect($result['thread']->decryptedSenderName())->toBe('');

    Livewire::actingAs($parent)
        ->test(ThreadsList::class)
        ->assertSee('Anonyme');
});

test('a "group" thread grants every current parent user', function () {
    $parent1 = User::factory()->parent()->create();
    $parent2 = User::factory()->parent()->create();
    $professeur = User::factory()->professeur()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];

    expect($thread->isAccessibleBy($parent1))->toBeTrue()
        ->and($thread->isAccessibleBy($parent2))->toBeTrue()
        ->and($thread->isAccessibleBy($professeur))->toBeFalse()
        ->and($thread->grants()->count())->toBe(2);
});

test('a "group" thread created with no parent users grants every administrateur instead', function () {
    $admin = User::factory()->admin()->create();
    $professeur = User::factory()->professeur()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];

    expect($thread->isAccessibleBy($admin))->toBeTrue()
        ->and($thread->isAccessibleBy($professeur))->toBeFalse()
        ->and($thread->grants()->count())->toBe(1);
});

test('a "member" thread grants only the chosen recipient, even when parents exist', function () {
    $parent = User::factory()->parent()->create();
    $professeur = User::factory()->professeur()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'member',
        recipientUserId: $professeur->id,
    );

    $thread = $result['thread'];

    expect($thread->isAccessibleBy($professeur))->toBeTrue()
        ->and($thread->isAccessibleBy($parent))->toBeFalse()
        ->and($thread->grants()->count())->toBe(1);
});

test('two different grantees can each independently decrypt the same message', function () {
    $parent1 = User::factory()->parent()->create();
    $parent2 = User::factory()->parent()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];
    $message = $thread->messages()->sole();

    $plaintext1 = $message->decrypt($thread->decryptKeyFor($parent1));
    $plaintext2 = $message->decrypt($thread->decryptKeyFor($parent2));

    expect($plaintext1)->toBe('Ceci est un message de test suffisamment long.')
        ->and($plaintext2)->toBe($plaintext1);
});
