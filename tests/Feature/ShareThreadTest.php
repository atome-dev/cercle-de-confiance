<?php

use App\Actions\CreateThreadWithMessage;
use App\Actions\ShareThread;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

function createGroupThread(): Thread
{
    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    return $result['thread'];
}

test('an existing grantee can share the thread with a new user and the target can then decrypt', function () {
    $parent = User::factory()->parent()->create();
    $professeur = User::factory()->professeur()->create();

    $thread = createGroupThread();
    expect($thread->isAccessibleBy($parent))->toBeTrue();

    $grants = app(ShareThread::class)->execute($thread, $parent, [$professeur->id]);

    expect($grants)->toHaveCount(1)
        ->and($thread->isAccessibleBy($professeur))->toBeTrue();

    $grant = $thread->grants()->where('user_id', $professeur->id)->sole();
    expect($grant->granted_by_user_id)->toBe($parent->id);

    $message = $thread->messages()->sole();
    expect($message->decrypt($thread->decryptKeyFor($professeur)))
        ->toBe('Ceci est un message de test suffisamment long.');
});

test('a non-grantee cannot share the thread', function () {
    $outsider = User::factory()->parent()->create();
    $target = User::factory()->professeur()->create();

    $thread = createGroupThread();
    $thread->grants()->delete();

    expect(fn () => app(ShareThread::class)->execute($thread, $outsider, [$target->id]))
        ->toThrow(AuthorizationException::class);

    expect($thread->isAccessibleBy($target))->toBeFalse();
});

test('sharing with an already-granted user is a no-op', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThread();

    $grants = app(ShareThread::class)->execute($thread, $parent, [$parent->id]);

    expect($grants)->toHaveCount(0)
        ->and($thread->grants()->count())->toBe(1);
});

test('sharing a mixed list of new and already-granted users only creates rows for the new ones', function () {
    $parent = User::factory()->parent()->create();
    $professeur = User::factory()->professeur()->create();
    $thread = createGroupThread();

    $grants = app(ShareThread::class)->execute($thread, $parent, [$parent->id, $professeur->id]);

    expect($grants)->toHaveCount(1)
        ->and($thread->grants()->count())->toBe(2);
});

test('a professeur who was shared the thread can in turn share it with someone else', function () {
    $parent = User::factory()->parent()->create();
    $professeur = User::factory()->professeur()->create();
    $secondProfesseur = User::factory()->professeur()->create();

    $thread = createGroupThread();
    app(ShareThread::class)->execute($thread, $parent, [$professeur->id]);

    app(ShareThread::class)->execute($thread, $professeur, [$secondProfesseur->id]);

    expect($thread->isAccessibleBy($secondProfesseur))->toBeTrue();

    $grant = $thread->grants()->where('user_id', $secondProfesseur->id)->sole();
    expect($grant->granted_by_user_id)->toBe($professeur->id);
});

test('an administrateur with no other role can be shared the thread, with no role restriction on the target', function () {
    $parent = User::factory()->parent()->create();
    $admin = User::factory()->admin()->create();

    $thread = createGroupThread();
    app(ShareThread::class)->execute($thread, $parent, [$admin->id]);

    expect($thread->isAccessibleBy($admin))->toBeTrue();
});
