<?php

namespace App\Livewire;

use App\Enums\AvailabilityMode;
use App\Enums\Role;
use App\Models\MeetingAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Personal weekly grid where a member paints their availability, slot by slot, as in person,
 * remote or unavailable.
 */
class MeetingAvailabilities extends Component
{
    public const string UNAVAILABLE = 'indisponible';

    /**
     * Monday of the displayed week, as `Y-m-d`. Shared in the URL with the slot finder tab.
     */
    #[Url(as: 'semaine')]
    public string $week = '';

    public function mount(): void
    {
        $this->week = $this->currentWeek()->format('Y-m-d');
    }

    /**
     * Only members can be meeting attendees, so only they declare availabilities.
     */
    #[Computed]
    public function canEdit(): bool
    {
        return Auth::user()->hasAnyRole([Role::Parent, Role::Professeur]);
    }

    public function previousWeek(): void
    {
        $this->week = $this->currentWeek()->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->week = $this->currentWeek()->addWeek()->format('Y-m-d');
    }

    public function thisWeek(): void
    {
        $this->week = now()->startOfWeek(CarbonImmutable::MONDAY)->format('Y-m-d');
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
     * Mode of each of the user's slots this week, keyed by `Y-m-d H:i`. Missing keys are unavailable.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function modesBySlot(): array
    {
        $monday = $this->currentWeek();

        return Auth::user()->meetingAvailabilities()
            ->where('starts_at', '>=', $monday)
            ->where('starts_at', '<', $monday->addWeek())
            ->get()
            ->mapWithKeys(fn (MeetingAvailability $availability): array => [
                $availability->starts_at->format('Y-m-d H:i') => $availability->mode->value,
            ])
            ->all();
    }

    /**
     * Applies one mode to a batch of slots. Invalid and past slots are silently skipped, so a drag
     * across a past cell still saves the rest of the selection.
     *
     * @param  list<string>  $slots  Slot starts as `Y-m-d H:i`.
     */
    public function paint(array $slots, string $mode): void
    {
        abort_unless($this->canEdit, 403);

        validator(['mode' => $mode], [
            'mode' => ['required', Rule::in([self::UNAVAILABLE, ...array_column(AvailabilityMode::cases(), 'value')])],
        ])->validate();

        $startsAt = collect($slots)
            ->filter(fn (mixed $slot): bool => is_string($slot) && $this->isEditableSlot($slot))
            ->unique()
            ->take(7 * count(MeetingAvailability::slotTimes()))
            ->map(fn (string $slot): string => $slot.':00')
            ->values();

        if ($startsAt->isEmpty()) {
            return;
        }

        if ($mode === self::UNAVAILABLE) {
            Auth::user()->meetingAvailabilities()->whereIn('starts_at', $startsAt)->delete();

            return;
        }

        $now = now();

        MeetingAvailability::upsert(
            $startsAt->map(fn (string $start): array => [
                'user_id' => Auth::id(),
                'starts_at' => $start,
                'mode' => $mode,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['user_id', 'starts_at'],
            ['mode', 'updated_at'],
        );
    }

    /**
     * Replaces the upcoming slots of the displayed week with those of the week before.
     */
    public function copyPreviousWeek(): void
    {
        abort_unless($this->canEdit, 403);

        $monday = $this->currentWeek();

        $previous = Auth::user()->meetingAvailabilities()
            ->where('starts_at', '>=', $monday->subWeek())
            ->where('starts_at', '<', $monday)
            ->get();

        Auth::user()->meetingAvailabilities()
            ->where('starts_at', '>=', max($monday, now()))
            ->where('starts_at', '<', $monday->addWeek())
            ->delete();

        foreach (AvailabilityMode::cases() as $mode) {
            $this->paint(
                $previous->where('mode', $mode)
                    ->map(fn (MeetingAvailability $availability): string => $availability->starts_at->addWeek()->format('Y-m-d H:i'))
                    ->values()
                    ->all(),
                $mode->value,
            );
        }
    }

    public function render()
    {
        return view('livewire.meeting-availabilities');
    }

    /**
     * A slot is editable when it is a valid start time of the grid and has not started yet.
     */
    private function isEditableSlot(string $slot): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2} (\d{2}:\d{2})$/', $slot, $matches)
            || ! in_array($matches[1], MeetingAvailability::slotTimes(), true)) {
            return false;
        }

        $start = CarbonImmutable::createFromFormat('!Y-m-d H:i', $slot);

        return $start !== null && $start->format('Y-m-d H:i') === $slot && $start->isFuture();
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
