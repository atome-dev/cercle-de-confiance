<?php

use App\Actions\CreateThreadWithMessage;
use App\Livewire\ThreadShow;
use App\Models\User;
use App\Services\ThreadCodeGenerator;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // These roles always exist in production (seeded by RoleSeeder on every deploy)
    // even before any user holds them — User::role() requires the role to exist,
    // unlike the whereHas('roles', ...) queries it replaced (shareableUsers() lists
    // parents, professeurs and administrateurs; group threads fall back to
    // administrateurs when there is no parent).
    Role::firstOrCreate(['name' => 'parent']);
    Role::firstOrCreate(['name' => 'administrateur']);
});

function createGroupThreadWithParentForTest(): array
{
    $parent = User::factory()->parent()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    return ['thread' => $result['thread'], 'fullCode' => $result['fullCode'], 'parent' => $parent];
}

test('a granted user can view decrypted messages and reply', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertSee('Ceci est un message de test suffisamment long.')
        ->set('newMessage', 'Merci pour votre message, nous revenons vers vous.')
        ->call('reply')
        ->assertSee('Merci pour votre message, nous revenons vers vous.');

    $reply = $thread->messages()->latest('id')->first();
    expect($reply->author_type)->toBe('member')
        ->and($reply->author_user_id)->toBe($parent->id);
});

test('a non-granted authenticated user is denied access and sees no messages', function () {
    ['thread' => $thread] = createGroupThreadWithParentForTest();
    $outsider = User::factory()->parent()->create();

    Livewire::actingAs($outsider)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertSet('accessDeniedReason', 'Vous n\'avez pas accès à ce dossier.')
        ->assertDontSee('Ceci est un message de test suffisamment long.');
});

test('anonymous access via the tracking code still works', function () {
    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];
    [, $privateKey] = app(ThreadCodeGenerator::class)->parseFullCode($result['fullCode']);

    $this->withSession(["anon_access_{$thread->id}" => $privateKey])
        ->get(route('threads.show', $thread))
        ->assertSeeText('Ceci est un message de test suffisamment long.');
});

test('updateStatus succeeds for a grantee and no-ops for a non-grantee', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();
    $outsider = User::factory()->parent()->create();

    Livewire::actingAs($outsider)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->call('updateStatus', 'archive');

    expect($thread->fresh()->status)->toBe('nouveau');

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->call('updateStatus', 'archive');

    expect($thread->fresh()->status)->toBe('archive');
});

test('share() creates new grants and updates the grantees list', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();
    $professeur = User::factory()->professeur()->create();

    $component = Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('shareUserIds', [$professeur->id])
        ->call('share');

    expect($thread->fresh()->isAccessibleBy($professeur))->toBeTrue();
    $component->assertSee($professeur->name);
});

test('sharing with an already-granted user via the UI does not error', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('shareUserIds', [$parent->id])
        ->call('share')
        ->assertSee($parent->name);

    expect($thread->grants()->count())->toBe(1);
});

test('shareableUsers excludes users who are already granted', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();
    $professeur = User::factory()->professeur()->create();

    $component = Livewire::actingAs($parent)->test(ThreadShow::class, ['thread' => $thread]);

    expect($component->instance()->shareableUsers()->pluck('id'))
        ->toContain($professeur->id)
        ->not->toContain($parent->id);
});
