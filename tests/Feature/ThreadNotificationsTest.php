<?php

use App\Actions\CreateThreadWithMessage;
use App\Actions\ReplyToThread;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\NewThreadMessageForMember;
use App\Notifications\NewThreadMessageForSender;
use App\Notifications\ThreadReceivedForSender;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // The "parent" and "administrateur" roles always exist in production (seeded by
    // RoleSeeder on every deploy) even before any user holds them — User::role()
    // requires the role to exist.
    Role::firstOrCreate(['name' => 'parent']);
    Role::firstOrCreate(['name' => 'administrateur']);
});

function createGroupThreadForNotificationTest(string $senderEmail = 'jean.dupont@example.com'): Thread
{
    return app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: $senderEmail,
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    )['thread'];
}

function replyAsMember(Thread $thread, User $member, bool $isInternal = false): void
{
    app(ReplyToThread::class)->execute(
        thread: $thread,
        threadKey: $thread->decryptKeyFor($member),
        message: 'Merci pour votre message.',
        authorType: 'member',
        authorUser: $member,
        isInternal: $isInternal,
    );
}

test('a message sent from the contact form notifies every granted member and confirms receipt to the sender', function () {
    $parents = User::factory()->parent()->count(2)->create();
    Notification::fake();

    $thread = createGroupThreadForNotificationTest();

    Notification::assertSentTo($parents, NewThreadMessageForMember::class);
    Notification::assertSentTo($thread, ThreadReceivedForSender::class);
    Notification::assertNotSentTo($thread, NewThreadMessageForSender::class);
});

test('a sender who left no email address gets no receipt confirmation', function () {
    User::factory()->parent()->create();
    Notification::fake();

    $thread = createGroupThreadForNotificationTest(senderEmail: '');

    Notification::assertNotSentTo($thread, ThreadReceivedForSender::class);
});

test('a member reply notifies the sender and the other granted members, but not its author', function () {
    [$author, $otherParent] = User::factory()->parent()->count(2)->create();
    $thread = createGroupThreadForNotificationTest();
    Notification::fake();

    replyAsMember($thread, $author);

    Notification::assertSentTo($thread, NewThreadMessageForSender::class);
    Notification::assertSentTo($otherParent, NewThreadMessageForMember::class);
    Notification::assertNotSentTo($author, NewThreadMessageForMember::class);
});

test('an internal reply never notifies the sender', function () {
    [$author, $otherParent] = User::factory()->parent()->count(2)->create();
    $thread = createGroupThreadForNotificationTest();
    Notification::fake();

    replyAsMember($thread, $author, isInternal: true);

    Notification::assertNotSentTo($thread, NewThreadMessageForSender::class);
    Notification::assertSentTo($otherParent, NewThreadMessageForMember::class);
});

test('a reply from the sender through the tracking code notifies the granted members', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForNotificationTest();
    Notification::fake();

    app(ReplyToThread::class)->execute(
        thread: $thread,
        threadKey: $thread->decryptKeyFor($parent),
        message: 'Voici une précision.',
        authorType: 'sender',
    );

    Notification::assertSentTo($parent, NewThreadMessageForMember::class);
    Notification::assertNotSentTo($thread, NewThreadMessageForSender::class);
});

test('a sender who left no email address is not notified', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForNotificationTest(senderEmail: '');
    Notification::fake();

    replyAsMember($thread, $parent);

    Notification::assertNotSentTo($thread, NewThreadMessageForSender::class);
});

test('notifications are queued instead of being sent during the request', function () {
    User::factory()->parent()->create();
    Queue::fake([SendQueuedNotifications::class]);

    createGroupThreadForNotificationTest();

    Queue::assertPushed(SendQueuedNotifications::class);
});

test('the emails never contain the exchanged message', function () {
    $parent = User::factory()->parent()->create();
    $thread = createGroupThreadForNotificationTest();

    $memberMail = (string) (new NewThreadMessageForMember($thread))->toMail($parent)->render();
    $senderMail = (string) (new NewThreadMessageForSender)->toMail($thread)->render();
    $receiptMail = (string) (new ThreadReceivedForSender)->toMail($thread)->render();

    expect($memberMail)->toContain($thread->code)
        ->not->toContain('Ceci est un message de test suffisamment long.');
    expect($senderMail)->toContain(route('anonymous-access'))
        ->not->toContain('Ceci est un message de test suffisamment long.');
    expect($receiptMail)->toContain(route('anonymous-access'))
        ->not->toContain($thread->code)
        ->not->toContain('Ceci est un message de test suffisamment long.');
    expect($thread->routeNotificationForMail())->toBe('jean.dupont@example.com');
});
