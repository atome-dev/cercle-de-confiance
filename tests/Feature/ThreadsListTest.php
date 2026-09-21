<?php

use App\Actions\CreateThreadWithMessage;
use App\Actions\ShareThread;
use App\Enums\SchoolClass;
use App\Enums\Section;
use App\Livewire\ThreadsList;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

test('the list shows the assigned section and class, or a dash when unclassified', function () {
    $parent = User::factory()->parent()->create();

    $classified = createGroupThreadForTest();
    $classified->update(['section' => Section::College, 'school_class' => SchoolClass::Classe7]);

    $unclassified = createGroupThreadForTest();

    $component = Livewire::actingAs($parent)->test(ThreadsList::class);

    $component->assertSeeInOrder([$classified->code, 'Collège', '7ème classe']);
    $component->assertSeeInOrder([$unclassified->code, '—']);
});

test('the list shows the sender\'s name when one was given', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForTest();

    Livewire::actingAs($parent)
        ->test(ThreadsList::class)
        ->assertSeeInOrder([$thread->code, 'Jean Dupont']);
});

test('a thread with an unread message from the sender shows the unread indicator', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForTest();

    Livewire::actingAs($parent)
        ->test(ThreadsList::class)
        ->assertSeeHtml('Messages non lus');
});

test('a thread the parent has already read shows no unread indicator', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForTest();
    $thread->markReadFor($parent);

    Livewire::actingAs($parent)
        ->test(ThreadsList::class)
        ->assertDontSeeHtml('Messages non lus');
});

test('the nav shows a dot next to Dossiers when the user has an unread thread', function () {
    $parent = User::factory()->parent()->create();
    createGroupThreadForTest();

    $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->get(route('home'))
        ->assertSee('Dossiers non lus');
});

test('the nav shows no dot next to Dossiers once all threads are read', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForTest();
    $thread->markReadFor($parent);

    $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->get(route('home'))
        ->assertDontSee('Dossiers non lus');
});

test('the list does not run extra queries per thread to compute unread state', function () {
    $parent = User::factory()->parent()->create();
    createGroupThreadForTest();
    createGroupThreadForTest();
    createGroupThreadForTest();
    createGroupThreadForTest();

    DB::enableQueryLog();
    Livewire::actingAs($parent)->test(ThreadsList::class);
    $queryCountForFour = count(DB::getQueryLog());
    DB::flushQueryLog();

    DB::disableQueryLog();
    createGroupThreadForTest();
    createGroupThreadForTest();
    createGroupThreadForTest();
    createGroupThreadForTest();
    DB::enableQueryLog();

    Livewire::actingAs($parent)->test(ThreadsList::class);
    $queryCountForEight = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Allow for incidental variance (e.g. permission cache warmup) but rule out
    // a per-thread query: doubling the threads must not add queries.
    expect($queryCountForEight)->toBeLessThanOrEqual($queryCountForFour);
});

test('archived dossiers are hidden by default and shown once the switch is toggled', function () {
    $parent = User::factory()->parent()->create();
    $archivedThread = createGroupThreadForTest();
    $archivedThread->update(['status' => 'archive']);

    $activeThread = createGroupThreadForTest();

    $component = Livewire::actingAs($parent)->test(ThreadsList::class);

    $component->assertDontSee($archivedThread->code)
        ->assertSee($activeThread->code);

    $component->set('showArchived', true)
        ->assertSee($archivedThread->code)
        ->assertSee($activeThread->code);
});
