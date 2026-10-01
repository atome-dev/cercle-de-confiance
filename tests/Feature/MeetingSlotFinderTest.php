<?php

use App\Enums\AvailabilityMode;
use App\Livewire\Meetings;
use App\Livewire\MeetingSlotFinder;
use App\Models\MeetingAvailability;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-05 07:00'));
    $this->actingAs(User::factory()->parent()->create());
});

/**
 * Declares a member available on every 30-minute slot from `$from` (included) to `$to` (excluded).
 */
function declareAvailability(User $user, string $date, string $from, string $to, AvailabilityMode $mode): void
{
    $start = Carbon::parse("{$date} {$from}");
    $end = Carbon::parse("{$date} {$to}");

    for (; $start->lt($end); $start->addMinutes(30)) {
        MeetingAvailability::factory()->for($user)->create(['starts_at' => $start->copy(), 'mode' => $mode]);
    }
}

test('a slot counts the members available for the whole meeting, in person only when in person throughout', function () {
    $alice = User::factory()->parent()->create(['name' => 'Alice']);
    $bruno = User::factory()->professeur()->create(['name' => 'Bruno']);
    $chloe = User::factory()->parent()->create(['name' => 'Chloé']);
    $admin = User::factory()->admin()->create();

    declareAvailability($alice, '2026-10-08', '18:00', '20:00', AvailabilityMode::InPerson);
    declareAvailability($bruno, '2026-10-08', '18:00', '18:30', AvailabilityMode::InPerson);
    declareAvailability($bruno, '2026-10-08', '18:30', '19:00', AvailabilityMode::Remote);
    declareAvailability($chloe, '2026-10-08', '18:30', '19:30', AvailabilityMode::InPerson);
    declareAvailability($admin, '2026-10-08', '18:00', '20:00', AvailabilityMode::InPerson);

    $candidates = Livewire::test(MeetingSlotFinder::class)
        ->set('duration', 60)
        ->instance()
        ->candidates;

    expect($candidates['2026-10-08 18:00']['inPerson']->pluck('name')->all())->toBe(['Alice'])
        ->and($candidates['2026-10-08 18:00']['remote']->pluck('name')->all())->toBe(['Bruno'])
        ->and($candidates['2026-10-08 18:30']['inPerson']->pluck('name')->all())->toBe(['Alice', 'Chloé'])
        ->and($candidates['2026-10-08 18:30']['remote']->all())->toBe([])
        ->and($candidates['2026-10-08 19:00']['total'])->toBe(1)
        ->and($candidates)->not->toHaveKey('2026-10-08 21:30');
});

test('slots are filtered on the minimum of attendees and of attendees in person', function () {
    $alice = User::factory()->parent()->create();
    $bruno = User::factory()->parent()->create();
    $chloe = User::factory()->parent()->create();

    declareAvailability($alice, '2026-10-06', '10:00', '11:00', AvailabilityMode::Remote);
    declareAvailability($bruno, '2026-10-06', '10:00', '11:00', AvailabilityMode::Remote);
    declareAvailability($chloe, '2026-10-06', '10:00', '11:00', AvailabilityMode::Remote);
    declareAvailability($alice, '2026-10-07', '14:00', '15:00', AvailabilityMode::InPerson);
    declareAvailability($bruno, '2026-10-07', '14:00', '15:00', AvailabilityMode::InPerson);

    $finder = Livewire::test(MeetingSlotFinder::class)
        ->set('duration', 60)
        ->set('minAttendees', 2)
        ->set('minInPerson', 0);

    expect(collect($finder->instance()->bestSlots)->map(fn (array $slot): string => $slot['start']->format('Y-m-d H:i'))->all())
        ->toBe(['2026-10-06 10:00', '2026-10-07 14:00']);

    $finder->set('minInPerson', 1);

    expect(collect($finder->instance()->bestSlots)->map(fn (array $slot): string => $slot['start']->format('Y-m-d H:i'))->all())
        ->toBe(['2026-10-07 14:00']);

    $finder->set('minAttendees', 3)
        ->assertSee('Aucun créneau ne correspond à ces critères cette semaine.');
});

test('the best slots are ranked and do not overlap each other', function () {
    $alice = User::factory()->parent()->create();
    $bruno = User::factory()->parent()->create();

    declareAvailability($alice, '2026-10-06', '18:00', '20:00', AvailabilityMode::InPerson);
    declareAvailability($bruno, '2026-10-06', '18:00', '20:00', AvailabilityMode::Remote);
    declareAvailability($alice, '2026-10-09', '09:00', '10:00', AvailabilityMode::InPerson);
    declareAvailability($bruno, '2026-10-09', '09:00', '10:00', AvailabilityMode::InPerson);

    $bestSlots = Livewire::test(MeetingSlotFinder::class)
        ->set('duration', 60)
        ->set('minAttendees', 2)
        ->instance()
        ->bestSlots;

    expect(collect($bestSlots)->map(fn (array $slot): string => $slot['start']->format('Y-m-d H:i'))->all())
        ->toBe(['2026-10-09 09:00', '2026-10-06 18:00', '2026-10-06 19:00']);
});

test('the finder lists the members who have not declared their availabilities yet', function () {
    $alice = User::factory()->parent()->create(['name' => 'Alice']);
    User::factory()->professeur()->create(['name' => 'Bruno']);
    declareAvailability($alice, '2026-10-06', '18:00', '19:00', AvailabilityMode::InPerson);

    Livewire::test(MeetingSlotFinder::class)
        ->assertSeeText('1 membre(s) sur 3 ont indiqué leurs disponibilités cette semaine.')
        ->assertSeeText('Pas encore renseigné')
        ->assertSeeText('Bruno')
        ->assertDontSeeText('Alice');
});

test('planning a slot opens the meeting form prefilled with its date, time and attendees', function () {
    $alice = User::factory()->parent()->create();
    $bruno = User::factory()->professeur()->create();
    declareAvailability($alice, '2026-10-08', '18:00', '19:00', AvailabilityMode::InPerson);
    declareAvailability($bruno, '2026-10-08', '18:00', '19:00', AvailabilityMode::Remote);

    Livewire::test(MeetingSlotFinder::class)
        ->set('duration', 60)
        ->call('plan', '2026-10-08 18:00')
        ->assertDispatched('plan-meeting', date: '2026-10-08', time: '18:00', attendeeIds: [$alice->id, $bruno->id]);

    Livewire::withQueryParams(['onglet' => 'creneaux'])
        ->test(Meetings::class)
        ->call('planMeeting', '2026-10-08', '18:00', [$alice->id, $bruno->id])
        ->assertSet('tab', 'calendrier')
        ->assertSet('showModal', true)
        ->assertSet('heldOn', '2026-10-08')
        ->assertSet('startsAt', '18:00')
        ->assertSet('attendeeIds', [$alice->id, $bruno->id])
        ->assertSet('month', '2026-10');
});

test('the slot finder tab is reachable from the meetings page', function () {
    $this->withCookies(withAccessCookie())
        ->get(route('meetings.index', ['onglet' => 'creneaux']))
        ->assertOk()
        ->assertSeeLivewire(MeetingSlotFinder::class);
});
