<?php

namespace App\Livewire;

use App\Enums\AvailabilityMode;
use App\Models\MeetingAvailability;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Crosses the members' availabilities of a week to find when a meeting of a given length gathers
 * the most people, filtered on a minimum of attendees and of attendees in person.
 */
class MeetingSlotFinder extends Component
{
    public const int MAX_DURATION = 240;

    public const int BEST_SLOTS = 5;

    /**
     * Monday of the displayed week, as `Y-m-d`. Shared in the URL with the availability tab.
     */
    #[Url(as: 'semaine')]
    public string $week = '';

    /**
     * Meeting length in minutes, a multiple of the slot length.
     */
    public int $duration = 60;

    public int $minAttendees = 2;

    public int $minInPerson = 0;

    /**
     * Start of the slot whose details are shown, as `Y-m-d H:i`.
     */
    public ?string $selected = null;

    public bool $showDetails = false;

    public function mount(): void
    {
        $this->week = $this->currentWeek()->format('Y-m-d');
    }

    public function previousWeek(): void
    {
        $this->week = $this->currentWeek()->subWeek()->format('Y-m-d');
        $this->selected = null;
    }

    public function nextWeek(): void
    {
        $this->week = $this->currentWeek()->addWeek()->format('Y-m-d');
        $this->selected = null;
    }

    public function thisWeek(): void
    {
        $this->week = now()->startOfWeek(CarbonImmutable::MONDAY)->format('Y-m-d');
        $this->selected = null;
    }

    public function select(string $slot): void
    {
        $this->selected = $slot;
        $this->showDetails = true;
    }

    /**
     * Hands the slot over to the calendar tab, which opens the meeting form prefilled with it.
     */
    public function plan(string $slot): void
    {
        $candidate = $this->candidates[$slot] ?? null;

        if ($candidate === null) {
            return;
        }

        $this->dispatch(
            'plan-meeting',
            date: $candidate['start']->format('Y-m-d'),
            time: $candidate['start']->format('H:i'),
            attendeeIds: [...$candidate['inPerson']->modelKeys(), ...$candidate['remote']->modelKeys()],
        );
    }

    /**
     * @return list<CarbonImmutable>
     */
    #[Computed]
    public function days(): array
    {
        $monday = $this->currentWeek();

        return array_map(fn (int $offset): CarbonImmutable => $monday->addDays($offset), range(0, 6));
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        return User::meetingMembers()->orderBy('name')->get();
    }

    /**
     * Members who declared at least one availability during the displayed week.
     *
     * @return list<int>
     */
    #[Computed]
    public function respondentIds(): array
    {
        return array_keys($this->availabilityModes);
    }

    /**
     * Every upcoming meeting start of the week, keyed by `Y-m-d H:i`, with the members available
     * during the whole meeting. A member counts in person only when in person on every slot.
     *
     * @return array<string, array{start: CarbonImmutable, inPerson: Collection<int, User>, remote: Collection<int, User>, total: int, matches: bool}>
     */
    #[Computed]
    public function candidates(): array
    {
        $times = MeetingAvailability::slotTimes();
        $slotCount = $this->slotCount();
        $modes = $this->availabilityModes;
        $members = $this->members->keyBy('id');
        $candidates = [];

        foreach ($this->days as $day) {
            for ($index = 0; $index + $slotCount <= count($times); $index++) {
                $start = CarbonImmutable::createFromFormat('!Y-m-d H:i', $day->format('Y-m-d').' '.$times[$index]);

                if (! $start->isFuture()) {
                    continue;
                }

                $keys = array_map(
                    fn (string $time): string => $day->format('Y-m-d').' '.$time,
                    array_slice($times, $index, $slotCount),
                );

                $inPerson = [];
                $remote = [];

                foreach ($modes as $userId => $userModes) {
                    $slotModes = array_map(fn (string $key): ?string => $userModes[$key] ?? null, $keys);

                    if (in_array(null, $slotModes, true)) {
                        continue;
                    }

                    if (array_unique($slotModes) === [AvailabilityMode::InPerson->value]) {
                        $inPerson[] = $userId;
                    } else {
                        $remote[] = $userId;
                    }
                }

                $total = count($inPerson) + count($remote);

                $candidates[$start->format('Y-m-d H:i')] = [
                    'start' => $start,
                    'inPerson' => new Collection($members->only($inPerson)->values()->all()),
                    'remote' => new Collection($members->only($remote)->values()->all()),
                    'total' => $total,
                    'matches' => $total > 0 && $total >= $this->minAttendees && count($inPerson) >= $this->minInPerson,
                ];
            }
        }

        return $candidates;
    }

    /**
     * Matching slots ranked by attendees, then attendees in person, then date. Overlapping slots
     * are dropped in favor of the better one so the list offers distinct options.
     *
     * @return list<array{start: CarbonImmutable, inPerson: Collection<int, User>, remote: Collection<int, User>, total: int, matches: bool}>
     */
    #[Computed]
    public function bestSlots(): array
    {
        $ranked = collect($this->candidates)
            ->where('matches', true)
            ->sort(fn (array $a, array $b): int => [$b['total'], $b['inPerson']->count(), $a['start']->getTimestamp()] <=> [$a['total'], $a['inPerson']->count(), $b['start']->getTimestamp()]);

        $length = $this->slotCount() * MeetingAvailability::SLOT_MINUTES;
        $picked = [];

        foreach ($ranked as $candidate) {
            $overlaps = collect($picked)->contains(
                fn (array $other): bool => $candidate['start']->lt($other['start']->addMinutes($length))
                    && $other['start']->lt($candidate['start']->addMinutes($length)),
            );

            if (! $overlaps) {
                $picked[] = $candidate;
            }

            if (count($picked) === self::BEST_SLOTS) {
                break;
            }
        }

        return $picked;
    }

    public function render()
    {
        return view('livewire.meeting-slot-finder');
    }

    private function slotCount(): int
    {
        $duration = min(max($this->duration, MeetingAvailability::SLOT_MINUTES), self::MAX_DURATION);

        return intdiv($duration, MeetingAvailability::SLOT_MINUTES);
    }

    /**
     * Availability mode of each member's slots during the displayed week.
     *
     * @return array<int, array<string, string>> Modes keyed by user id, then by `Y-m-d H:i`.
     */
    #[Computed]
    public function availabilityModes(): array
    {
        $monday = $this->currentWeek();

        return MeetingAvailability::query()
            ->whereIn('user_id', $this->members->modelKeys())
            ->where('starts_at', '>=', $monday)
            ->where('starts_at', '<', $monday->addWeek())
            ->get()
            ->groupBy('user_id')
            ->map(fn ($availabilities): array => $availabilities->mapWithKeys(fn (MeetingAvailability $availability): array => [
                $availability->starts_at->format('Y-m-d H:i') => $availability->mode->value,
            ])->all())
            ->all();
    }

    /**
     * Falls back to the current week when the `semaine` query parameter is missing or malformed.
     */
    private function currentWeek(): CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->week)) {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $this->week);

            if ($date !== null && $date->format('Y-m-d') === $this->week) {
                return $date->startOfWeek(CarbonImmutable::MONDAY);
            }
        }

        return CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY);
    }
}
