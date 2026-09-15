<?php

use App\Livewire\ContactForm;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function createEligibleMember(string $role = 'parent'): User
{
    Role::firstOrCreate(['name' => $role]);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

beforeEach(function () {
    // These roles always exist in production (seeded by RoleSeeder on every deploy)
    // even before any user holds them — User::role() requires the role to exist,
    // unlike the whereHas('roles', ...) queries it replaced.
    Role::firstOrCreate(['name' => 'parent']);
    Role::firstOrCreate(['name' => 'professeur']);
    Role::firstOrCreate(['name' => 'administrateur']);
});

test('the recipient dropdown lists parents and professeurs, and appears when "member" is chosen', function () {
    $parent = createEligibleMember('parent');
    $professeur = createEligibleMember('professeur');
    $admin = createEligibleMember('administrateur');
    $otherUser = User::factory()->create();

    $component = Livewire::test(ContactForm::class);

    $component->assertDontSee($otherUser->name);
    $component->assertSet('recipientType', 'group');

    $component->set('recipientType', 'member');

    $component->assertSee($parent->name);
    $component->assertSee($professeur->name);
    $component->assertDontSee($admin->name);
    $component->assertDontSee($otherUser->name);
});

test('the contact form does not crash when no parent or professeur is registered yet', function () {
    Livewire::test(ContactForm::class)
        ->set('recipientType', 'member')
        ->assertSet('recipientType', 'member');
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

test('submitting shows a link to access the dossier with the tracking code', function () {
    Livewire::test(ContactForm::class)
        ->set('senderName', 'Jean Dupont')
        ->set('senderEmail', 'jean@example.com')
        ->set('message', 'Ceci est un message de test suffisamment long.')
        ->set('recipientType', 'group')
        ->call('submit')
        ->assertSeeHtml(route('anonymous-access'));
});

test('switching back to "Cercle de Confiance" clears the previously selected member', function () {
    $member = createEligibleMember();

    Livewire::test(ContactForm::class)
        ->set('recipientType', 'member')
        ->set('recipientUserId', $member->id)
        ->set('recipientType', 'group')
        ->assertSet('recipientUserId', null);
});
