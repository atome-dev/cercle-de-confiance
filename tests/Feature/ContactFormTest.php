<?php

use App\Livewire\ContactForm;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function createEligibleMember(string $role = 'membre'): User
{
    Role::firstOrCreate(['name' => $role]);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('the recipient dropdown only lists members and administrateurs, and appears when "member" is chosen', function () {
    $member = createEligibleMember('membre');
    $admin = createEligibleMember('administrateur');
    $otherUser = User::factory()->create();

    $component = Livewire::test(ContactForm::class);

    $component->assertDontSee($otherUser->name);
    $component->assertSet('recipientType', 'group');

    $component->set('recipientType', 'member');

    $component->assertSee($member->name);
    $component->assertSee($admin->name);
    $component->assertDontSee($otherUser->name);
});

test('submitting with "Cercle de Confiance" creates a group thread with no recipient user', function () {
    Livewire::test(ContactForm::class)
        ->set('senderName', 'Jean Dupont')
        ->set('senderEmail', 'jean@example.com')
        ->set('message', 'Ceci est un message de test suffisamment long.')
        ->set('recipientType', 'group')
        ->call('submit')
        ->assertHasNoErrors();

    $thread = Thread::sole();

    expect($thread->recipient_type)->toBe('group')
        ->and($thread->recipient_user_id)->toBeNull();
});

test('submitting with "member" requires a selected member', function () {
    Livewire::test(ContactForm::class)
        ->set('senderName', 'Jean Dupont')
        ->set('senderEmail', 'jean@example.com')
        ->set('message', 'Ceci est un message de test suffisamment long.')
        ->set('recipientType', 'member')
        ->call('submit')
        ->assertHasErrors(['recipientUserId' => 'required']);

    expect(Thread::count())->toBe(0);
});

test('submitting with "member" and a chosen member creates a thread for that member', function () {
    $member = createEligibleMember();

    Livewire::test(ContactForm::class)
        ->set('senderName', 'Jean Dupont')
        ->set('senderEmail', 'jean@example.com')
        ->set('message', 'Ceci est un message de test suffisamment long.')
        ->set('recipientType', 'member')
        ->set('recipientUserId', $member->id)
        ->call('submit')
        ->assertHasNoErrors();

    $thread = Thread::sole();

    expect($thread->recipient_type)->toBe('member')
        ->and($thread->recipient_user_id)->toBe($member->id);
});

test('switching back to "Cercle de Confiance" clears the previously selected member', function () {
    $member = createEligibleMember();

    Livewire::test(ContactForm::class)
        ->set('recipientType', 'member')
        ->set('recipientUserId', $member->id)
        ->set('recipientType', 'group')
        ->assertSet('recipientUserId', null);
});
