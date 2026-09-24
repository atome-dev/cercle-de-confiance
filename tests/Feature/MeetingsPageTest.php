<?php

use App\Livewire\Meetings;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('the meetings page redirects guests to login', function () {
    $this->withCookies(withAccessCookie())
        ->get(route('meetings.index'))
        ->assertRedirect(route('login'));
});

test('users without a member role cannot see the meetings page', function () {
    $this->actingAs(User::factory()->create())
        ->withCookies(withAccessCookie())
        ->get(route('meetings.index'))
        ->assertForbidden();
});

test('members see the meetings of the current month with their attendees', function () {
    $this->travelTo(Carbon::parse('2026-09-15'));

    $member = User::factory()->parent()->create(['name' => 'Alice Martin']);
    $meeting = Meeting::factory()->create(['held_on' => '2026-09-10', 'title' => 'Réunion de rentrée']);
    $meeting->attendees()->attach($member);
    Meeting::factory()->create(['held_on' => '2026-10-10', 'title' => 'Réunion d’octobre']);

    $this->actingAs($member)
        ->withCookies(withAccessCookie())
        ->get(route('meetings.index'))
        ->assertOk()
        ->assertSeeText('septembre 2026')
        ->assertSeeText('Réunion de rentrée')
        ->assertSeeText('Alice Martin')
        ->assertDontSeeText('Réunion d’octobre');
});

test('the calendar navigates between months', function () {
    $this->travelTo(Carbon::parse('2026-01-15'));
    $this->actingAs(User::factory()->professeur()->create());
    Meeting::factory()->create(['held_on' => '2025-12-18', 'title' => 'Réunion de Noël']);

    Livewire::test(Meetings::class)
        ->assertSet('month', '2026-01')
        ->assertDontSee('Réunion de Noël')
        ->call('previousMonth')
        ->assertSet('month', '2025-12')
        ->assertSee('Réunion de Noël')
        ->call('nextMonth')
        ->call('nextMonth')
        ->assertSet('month', '2026-02');
});

test('an administrator can record a meeting with its attendees', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->parent()->create();
    $this->actingAs($admin);

    Livewire::test(Meetings::class)
        ->call('create', '2026-09-24')
        ->set('title', 'Point mensuel')
        ->set('startsAt', '18:30')
        ->set('attendeeIds', [$admin->id, $member->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $meeting = Meeting::sole();
    expect($meeting->title)->toBe('Point mensuel')
        ->and($meeting->held_on->format('Y-m-d'))->toBe('2026-09-24')
        ->and($meeting->attendees->pluck('id')->sort()->values()->all())->toBe([$admin->id, $member->id]);
});

test('an administrator can update and delete a meeting', function () {
    $this->actingAs(User::factory()->admin()->create());
    $absent = User::factory()->parent()->create();
    $present = User::factory()->professeur()->create();
    $meeting = Meeting::factory()->create(['title' => 'Ancien titre']);
    $meeting->attendees()->attach($absent);

    Livewire::test(Meetings::class)
        ->call('edit', $meeting->id)
        ->assertSet('attendeeIds', [$absent->id])
        ->set('title', 'Nouveau titre')
        ->set('attendeeIds', [$present->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($meeting->fresh()->title)->toBe('Nouveau titre')
        ->and($meeting->attendees()->pluck('users.id')->all())->toBe([$present->id]);

    Livewire::test(Meetings::class)->call('delete', $meeting->id);

    expect(Meeting::find($meeting->id))->toBeNull();
});

test('a meeting requires a title and a valid date', function () {
    $this->actingAs(User::factory()->admin()->create());
    User::factory()->parent()->create();

    Livewire::test(Meetings::class)
        ->call('create')
        ->set('heldOn', 'pas-une-date')
        ->call('save')
        ->assertHasErrors(['title', 'heldOn']);
});

test('attendees must be members', function () {
    $this->actingAs(User::factory()->admin()->create());
    User::factory()->parent()->create();
    $outsider = User::factory()->create();

    Livewire::test(Meetings::class)
        ->call('create')
        ->set('title', 'Réunion')
        ->set('attendeeIds', [$outsider->id])
        ->call('save')
        ->assertHasErrors(['attendeeIds.0']);
});

test('non-administrator members cannot manage meetings', function () {
    $this->actingAs(User::factory()->parent()->create());
    $meeting = Meeting::factory()->create();

    Livewire::test(Meetings::class)
        ->assertDontSee('Ajouter une réunion')
        ->call('create')
        ->assertForbidden();

    Livewire::test(Meetings::class)
        ->call('delete', $meeting->id)
        ->assertForbidden();

    expect(Meeting::find($meeting->id))->not->toBeNull();
});
