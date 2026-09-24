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
    Meeting::factory()->create(['held_on' => '2026-08-27', 'title' => 'Réunion d’août']);

    $this->actingAs($member)
        ->withCookies(withAccessCookie())
        ->get(route('meetings.index'))
        ->assertOk()
        ->assertSeeText('septembre 2026')
        ->assertSeeText('Réunion de rentrée')
        ->assertSeeText('Alice Martin')
        ->assertDontSeeText('Réunion d’août');
});

test('the next meeting is detailed above the calendar whatever the displayed month', function () {
    $this->travelTo(Carbon::parse('2026-09-24'));
    $member = User::factory()->parent()->create(['name' => 'Alice Martin']);
    $this->actingAs($member);

    Meeting::factory()->create(['held_on' => '2026-09-20', 'title' => 'Réunion passée']);
    Meeting::factory()->create(['held_on' => '2026-11-05', 'title' => 'Réunion plus lointaine']);
    $next = Meeting::factory()->create([
        'held_on' => '2026-10-08',
        'title' => 'Réunion d’octobre',
        'notes' => 'Préparer le bilan du trimestre.',
    ]);
    $next->attendees()->attach($member);

    Livewire::test(Meetings::class)
        ->call('previousMonth')
        ->call('previousMonth')
        ->assertSeeHtmlInOrder(['Prochaine réunion', 'Réunion d’octobre', 'Préparer le bilan du trimestre.', 'Alice Martin', 'juillet 2026'])
        ->assertDontSee('Réunion plus lointaine');
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
        ->set('attendeeIds', [$member->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $meeting = Meeting::sole();
    expect($meeting->title)->toBe('Point mensuel')
        ->and($meeting->held_on->format('Y-m-d'))->toBe('2026-09-24')
        ->and($meeting->attendees->pluck('id')->all())->toBe([$member->id]);
});

test('administrators cannot be recorded as attendees', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(Meetings::class)
        ->call('create', '2026-09-24')
        ->set('title', 'Point mensuel')
        ->set('attendeeIds', [$admin->id])
        ->call('save')
        ->assertHasErrors(['attendeeIds.0']);
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

    Livewire::test(Meetings::class)
        ->call('create')
        ->set('heldOn', 'pas-une-date')
        ->call('save')
        ->assertHasErrors(['title', 'heldOn']);
});

test('attendees must be members', function () {
    $this->actingAs(User::factory()->admin()->create());
    $outsider = User::factory()->create();

    Livewire::test(Meetings::class)
        ->call('create')
        ->set('title', 'Réunion')
        ->set('attendeeIds', [$outsider->id])
        ->call('save')
        ->assertHasErrors(['attendeeIds.0']);
});

test('parents and professeurs can manage meetings too', function (string $role) {
    $member = User::factory()->{$role}()->create();
    $this->actingAs($member);

    Livewire::test(Meetings::class)
        ->assertSee('Ajouter une réunion')
        ->call('create', '2026-09-24')
        ->set('title', 'Réunion des membres')
        ->set('attendeeIds', [$member->id])
        ->call('save')
        ->assertHasNoErrors();

    $meeting = Meeting::sole();

    Livewire::test(Meetings::class)
        ->call('edit', $meeting->id)
        ->set('title', 'Réunion modifiée')
        ->call('save')
        ->assertHasNoErrors();

    expect($meeting->fresh()->title)->toBe('Réunion modifiée');

    Livewire::test(Meetings::class)->call('delete', $meeting->id);

    expect(Meeting::find($meeting->id))->toBeNull();
})->with(['parent', 'professeur']);

test('users without a member role cannot manage meetings', function () {
    $this->actingAs(User::factory()->create());
    $meeting = Meeting::factory()->create();

    Livewire::test(Meetings::class)
        ->call('delete', $meeting->id)
        ->assertForbidden();

    expect(Meeting::find($meeting->id))->not->toBeNull();
});
