<?php

use App\Livewire\MeetingAvailabilities;
use App\Models\MeetingAvailability;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-07 12:00'));
});

/**
 * @return array<string, string>
 */
function availabilityModesOf(User $user): array
{
    return $user->meetingAvailabilities()
        ->orderBy('starts_at')
        ->get()
        ->mapWithKeys(fn (MeetingAvailability $availability): array => [
            $availability->starts_at->format('Y-m-d H:i') => $availability->mode->value,
        ])
        ->all();
}

test('a member paints slots in person, switches them to remote, then clears them', function () {
    $member = User::factory()->parent()->create();
    $this->actingAs($member);

    $component = Livewire::test(MeetingAvailabilities::class)
        ->call('paint', ['2026-10-08 18:00', '2026-10-08 18:30'], 'presentiel');

    expect(availabilityModesOf($member))->toBe([
        '2026-10-08 18:00' => 'presentiel',
        '2026-10-08 18:30' => 'presentiel',
    ]);

    $component->call('paint', ['2026-10-08 18:30'], 'distanciel');

    expect(availabilityModesOf($member))->toBe([
        '2026-10-08 18:00' => 'presentiel',
        '2026-10-08 18:30' => 'distanciel',
    ]);

    $component->call('paint', ['2026-10-08 18:00', '2026-10-08 18:30'], 'indisponible');

    expect(availabilityModesOf($member))->toBe([]);
});

test('the grid shows the member own slots of the displayed week', function () {
    $member = User::factory()->professeur()->create();
    MeetingAvailability::factory()->for($member)->remote()->create(['starts_at' => '2026-10-09 20:00']);
    MeetingAvailability::factory()->inPerson()->create(['starts_at' => '2026-10-09 20:30']);

    $this->actingAs($member);

    Livewire::test(MeetingAvailabilities::class)
        ->assertSet('week', '2026-10-05')
        ->assertSeeHtml('data-slot="2026-10-09 20:00" data-mode="distanciel"')
        ->assertSeeHtml('data-slot="2026-10-09 20:30" data-mode="indisponible"');
});

test('past, off-grid and malformed slots are ignored', function () {
    $member = User::factory()->parent()->create();
    $this->actingAs($member);

    Livewire::test(MeetingAvailabilities::class)
        ->call('paint', [
            '2026-10-07 11:30',
            '2026-10-08 07:30',
            '2026-10-08 22:00',
            '2026-10-08 10:15',
            '2026-02-31 10:00',
            'pas-un-creneau',
            '2026-10-08 10:00',
        ], 'presentiel');

    expect(availabilityModesOf($member))->toBe(['2026-10-08 10:00' => 'presentiel']);
});

test('clearing slots leaves other members availabilities untouched', function () {
    $member = User::factory()->parent()->create();
    $other = MeetingAvailability::factory()->inPerson()->create(['starts_at' => '2026-10-08 18:00']);
    $this->actingAs($member);

    Livewire::test(MeetingAvailabilities::class)
        ->call('paint', ['2026-10-08 18:00'], 'indisponible');

    expect($other->fresh())->not->toBeNull();
});

test('an unknown mode is rejected', function () {
    $this->actingAs(User::factory()->parent()->create());

    Livewire::test(MeetingAvailabilities::class)
        ->call('paint', ['2026-10-08 18:00'], 'peut-etre')
        ->assertHasErrors(['mode']);

    expect(MeetingAvailability::count())->toBe(0);
});

test('administrators without a member role cannot declare availabilities', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(MeetingAvailabilities::class)
        ->assertSee('Seuls les parents et professeurs')
        ->call('paint', ['2026-10-08 18:00'], 'presentiel')
        ->assertForbidden();

    expect(MeetingAvailability::count())->toBe(0);
});

test('a member reuses the previous week for the upcoming slots of the displayed week', function () {
    $member = User::factory()->parent()->create();
    MeetingAvailability::factory()->for($member)->inPerson()->create(['starts_at' => '2026-09-30 10:00']);
    MeetingAvailability::factory()->for($member)->remote()->create(['starts_at' => '2026-10-01 18:00']);
    MeetingAvailability::factory()->for($member)->remote()->create(['starts_at' => '2026-10-06 09:00']);
    MeetingAvailability::factory()->for($member)->inPerson()->create(['starts_at' => '2026-10-09 09:00']);
    $this->actingAs($member);

    Livewire::test(MeetingAvailabilities::class)->call('copyPreviousWeek');

    expect(availabilityModesOf($member))->toBe([
        '2026-09-30 10:00' => 'presentiel',
        '2026-10-01 18:00' => 'distanciel',
        '2026-10-06 09:00' => 'distanciel',
        '2026-10-08 18:00' => 'distanciel',
    ]);
});

test('the availabilities tab is reachable from the meetings page', function () {
    $this->actingAs(User::factory()->parent()->create())
        ->withCookies(withAccessCookie())
        ->get(route('meetings.index', ['onglet' => 'disponibilites']))
        ->assertOk()
        ->assertSeeLivewire(MeetingAvailabilities::class);
});
