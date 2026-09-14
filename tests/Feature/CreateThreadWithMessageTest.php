<?php

use App\Actions\CreateThreadWithMessage;
use App\Models\Thread;
use App\Models\User;
use App\Services\ThreadCodeGenerator;
use Illuminate\Contracts\Encryption\DecryptException;

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

test('the sender name and email can be recovered with the full tracking code', function () {
    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    [, $privateKey] = app(ThreadCodeGenerator::class)->parseFullCode($result['fullCode']);

    $thread = $result['thread'];

    expect($thread->decryptSenderName($privateKey))->toBe('Jean Dupont')
        ->and($thread->decryptSenderEmail($privateKey))->toBe('jean.dupont@example.com');
});

test('the sender name and email cannot be recovered with the wrong private key', function () {
    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];

    expect(fn () => $thread->decryptSenderName('XXXX'))->toThrow(DecryptException::class);
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
