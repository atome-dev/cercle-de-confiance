<?php

use App\Actions\CreateThreadWithMessage;
use App\Actions\ShareThread;
use App\Livewire\ThreadsList;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;

function createGroupThreadForTest(): Thread
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

test('a parent with a grant sees the thread in their list', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForTest();

    Livewire::actingAs($parent)
        ->test(ThreadsList::class)
        ->assertSee($thread->code);
});

test('a parent without any grant sees an empty list', function () {
    User::factory()->parent()->create();
    $thread = createGroupThreadForTest();

    $ungrantedParent = User::factory()->parent()->create();

    Livewire::actingAs($ungrantedParent)
        ->test(ThreadsList::class)
        ->assertDontSee($thread->code);
});

test('an administrateur with no grants sees an empty list even though the page is accessible', function () {
    User::factory()->parent()->create(); // ensures the group auto-grant does not fall back to administrateurs
    $admin = User::factory()->admin()->create();
    $thread = createGroupThreadForTest();

    Livewire::actingAs($admin)
        ->test(ThreadsList::class)
        ->assertDontSee($thread->code);
});

test('an administrateur who was explicitly shared a thread sees exactly that thread', function () {
    $parent = User::factory()->parent()->create();
    $admin = User::factory()->admin()->create();
    $thread = createGroupThreadForTest();

    app(ShareThread::class)->execute($thread, $parent, [$admin->id]);

    Livewire::actingAs($admin)
        ->test(ThreadsList::class)
        ->assertSee($thread->code);
});

test('a professeur who is the direct recipient of a "member" thread sees it', function () {
    $professeur = User::factory()->professeur()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'member',
        recipientUserId: $professeur->id,
    );

    Livewire::actingAs($professeur)
        ->test(ThreadsList::class)
        ->assertSee($result['thread']->code);
});

test('a professeur who is not the direct recipient does not see a "member" thread', function () {
    $recipient = User::factory()->professeur()->create();
    $other = User::factory()->professeur()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'member',
        recipientUserId: $recipient->id,
    );

    Livewire::actingAs($other)
        ->test(ThreadsList::class)
        ->assertDontSee($result['thread']->code);
});

test('a user with none of administrateur, parent or professeur gets a 403 on the dossiers page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('threads.index'))
        ->assertForbidden();
});

test('the status filter still works combined with the grant-based query', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForTest();
    $thread->update(['status' => 'archive']);

    $otherThread = createGroupThreadForTest();

    $component = Livewire::actingAs($parent)->test(ThreadsList::class);

    $component->assertSee($thread->code)->assertSee($otherThread->code);

    $component->set('statusFilter', 'archive')
        ->assertSee($thread->code)
        ->assertDontSee($otherThread->code);
});
