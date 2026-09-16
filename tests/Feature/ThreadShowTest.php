<?php

use App\Actions\CreateThreadWithMessage;
use App\Actions\ReplyToThread;
use App\Actions\ShareThread;
use App\Enums\SchoolClass;
use App\Enums\Section;
use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Livewire\ThreadShow;
use App\Models\User;
use App\Services\ThreadCodeGenerator;
use App\Services\ThreadEncryptionService;
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

    // /dossiers/{thread} now sits behind the school's shared access.code gate even
    // for anonymous tracking-code visitors — only matters for the plain HTTP
    // ->get(route('threads.show', ...)) calls below; Livewire::test() bypasses
    // route middleware entirely, so it's a no-op for the granted-member tests.
    $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue());
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

test('the anonymous sender sees "Vous" instead of "Expéditeur" on their own messages', function () {
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
        ->assertSeeText('Vous')
        ->assertDontSeeText('Expéditeur');
});

test('a granted member sees "Expéditeur" on the sender\'s messages', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertSee('Expéditeur')
        ->assertDontSee('Vous');
});

test('a granted member sees the sender\'s name and email below the dossier code', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertSee('Jean Dupont')
        ->assertSee('jean.dupont@example.com');
});

test('the anonymous sender does not see their own name and email redisplayed', function () {
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
        ->assertDontSee('Jean Dupont')
        ->assertDontSee('jean.dupont@example.com');
});

test('a granted member can reply internally, and the reply is tagged accordingly', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('newMessage', 'Note interne : attention, dossier sensible.')
        ->set('replyVisibility', 'internal')
        ->call('reply')
        ->assertSee('Note interne : attention, dossier sensible.')
        ->assertSee('Interne')
        ->assertSet('replyVisibility', 'sender');

    $reply = $thread->messages()->latest('id')->first();
    expect($reply->author_type)->toBe('member')
        ->and($reply->is_internal)->toBeTrue();
});

test('the anonymous sender never sees an internal reply', function () {
    ['thread' => $thread, 'fullCode' => $fullCode, 'parent' => $parent] = createGroupThreadWithParentForTest();

    $threadKey = app(ThreadEncryptionService::class)->openAppEnvelope(
        $thread->grants()->where('user_id', $parent->id)->sole()->key_envelope
    );

    app(ReplyToThread::class)->execute(
        thread: $thread,
        threadKey: $threadKey,
        message: 'Note interne : ne pas partager.',
        authorType: 'member',
        authorUser: $parent,
        isInternal: true,
    );

    [, $privateKey] = app(ThreadCodeGenerator::class)->parseFullCode($fullCode);

    $this->withSession(["anon_access_{$thread->id}" => $privateKey])
        ->get(route('threads.show', $thread))
        ->assertDontSeeText('Note interne : ne pas partager.')
        ->assertDontSeeText('Interne');
});

test('the anonymous sender does not see the internal/sender reply choice', function () {
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
        ->assertDontSee('Interne');
});

test('an anonymous reply is never stored as internal even if the property is tampered with', function () {
    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];
    [, $privateKey] = app(ThreadCodeGenerator::class)->parseFullCode($result['fullCode']);

    $this->withSession(["anon_access_{$thread->id}" => $privateKey]);

    Livewire::test(ThreadShow::class, ['thread' => $thread])
        ->set('newMessage', 'Réponse anonyme.')
        ->set('replyVisibility', 'internal')
        ->call('reply');

    $reply = $thread->messages()->latest('id')->first();
    expect($reply->author_type)->toBe('sender')
        ->and($reply->is_internal)->toBeFalse();
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

test('archiving sets archived_at, and reopening clears it', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->call('updateStatus', 'archive');

    expect($thread->fresh()->archived_at)->not->toBeNull();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->call('updateStatus', 'en_cours');

    expect($thread->fresh()->archived_at)->toBeNull();
});

test('updateClassification succeeds for a grantee and no-ops for a non-grantee', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();
    $outsider = User::factory()->parent()->create();

    Livewire::actingAs($outsider)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('classificationSection', Section::College->value)
        ->set('classificationSchoolClass', SchoolClass::Classe7->value)
        ->call('updateClassification');

    expect($thread->fresh()->section)->toBeNull()
        ->and($thread->fresh()->school_class)->toBeNull();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('classificationSection', Section::College->value)
        ->set('classificationSchoolClass', SchoolClass::Classe7->value)
        ->call('updateClassification');

    expect($thread->fresh()->section)->toBe(Section::College)
        ->and($thread->fresh()->school_class)->toBe(SchoolClass::Classe7);
});

test('selecting a class moves the section select to that class\'s section', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertSet('classificationSection', null)
        ->set('classificationSchoolClass', SchoolClass::Classe7->value)
        ->assertSet('classificationSection', Section::College->value);
});

test('selecting a section clears the previously selected class', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('classificationSchoolClass', SchoolClass::Classe7->value)
        ->assertSet('classificationSchoolClass', SchoolClass::Classe7->value)
        ->set('classificationSection', Section::Lycee->value)
        ->assertSet('classificationSchoolClass', '');
});

test('updateClassification can clear a previously assigned section and class', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();
    $thread->update(['section' => Section::Lycee, 'school_class' => SchoolClass::Classe11]);

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('classificationSection', '')
        ->set('classificationSchoolClass', '')
        ->call('updateClassification');

    expect($thread->fresh()->section)->toBeNull()
        ->and($thread->fresh()->school_class)->toBeNull();
});

test('updateClassification rejects a value that is not a valid section or class', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('classificationSection', 'not-a-real-section')
        ->call('updateClassification')
        ->assertHasErrors(['classificationSection']);

    expect($thread->fresh()->section)->toBeNull();
});

test('a granted parent or professeur can see and save a comment', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertSee('Commentaire')
        ->set('comment', 'À surveiller de près.')
        ->call('updateComment');

    $thread->refresh();
    expect($thread->comment_ciphertext)->not->toBeNull()
        ->and($thread->decryptComment($thread->decryptKeyFor($parent)))->toBe('À surveiller de près.');
});

test('a non-granted parent cannot save a comment', function () {
    ['thread' => $thread] = createGroupThreadWithParentForTest();
    $outsider = User::factory()->parent()->create();

    Livewire::actingAs($outsider)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('comment', 'Intrusion.')
        ->call('updateComment');

    expect($thread->fresh()->comment_ciphertext)->toBeNull();
});

test('a granted administrateur does not see the comment field and cannot save one', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();
    $admin = User::factory()->admin()->create();
    app(ShareThread::class)->execute($thread, $parent, [$admin->id]);

    Livewire::actingAs($admin)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->assertDontSee('Commentaire')
        ->set('comment', 'Tentative admin.')
        ->call('updateComment');

    expect($thread->fresh()->comment_ciphertext)->toBeNull();
});

test('a comment is encrypted at rest with the thread key, like the exchanged messages', function () {
    ['thread' => $thread, 'parent' => $parent] = createGroupThreadWithParentForTest();

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->set('comment', 'Contact établi avec la famille.')
        ->call('updateComment');

    $thread->refresh();
    expect($thread->comment_ciphertext)->not->toContain('Contact établi avec la famille.');
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
