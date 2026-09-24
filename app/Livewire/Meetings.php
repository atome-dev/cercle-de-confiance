<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Meeting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Réunions')]
class Meetings extends Component
{
    /**
     * Displayed month, as `Y-m`. Kept in the URL so a month can be bookmarked or shared.
     */
    #[Url]
    public string $month = '';

    public bool $showModal = false;

    public ?Meeting $editing = null;

    public bool $showDetails = false;

    public ?Meeting $viewing = null;

    public string $heldOn = '';

    public string $startsAt = '';

    public string $title = '';

    public string $notes = '';

    /**
     * @var list<int>
     */
    public array $attendeeIds = [];

    public function mount(): void
    {
        $this->month = $this->currentMonth()->format('Y-m');
    }

    #[Computed]
    public function canManage(): bool
    {
        return Auth::user()->hasAnyRole([Role::Administrateur, Role::Parent, Role::Professeur]);
    }

    public function previousMonth(): void
    {
        $this->month = $this->currentMonth()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->currentMonth()->addMonthNoOverflow()->format('Y-m');
    }

    public function today(): void
    {
        $this->month = now()->format('Y-m');
    }

    /**
     * Weeks of the displayed month, Monday to Sunday, padded with the adjacent months' days.
     *
     * @return list<list<CarbonImmutable>>
     */
    #[Computed]
    public function weeks(): array
    {
        $month = $this->currentMonth();
        $monday = $month->startOfWeek(CarbonImmutable::MONDAY);
        $end = $month->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);

        $weeks = [];
        while ($monday->lte($end)) {
            $weeks[] = collect(range(0, 6))->map(fn (int $offset): CarbonImmutable => $monday->addDays($offset))->all();
            $monday = $monday->addWeek();
        }

        return $weeks;
    }

    /**
     * First meeting from today onwards, independent of the displayed month.
     */
    #[Computed]
    public function nextMeeting(): ?Meeting
    {
        return Meeting::with(['attendees' => fn ($query) => $query->orderBy('name')])
            ->whereDate('held_on', '>=', today())
            ->orderBy('held_on')
            ->orderBy('starts_at')
            ->first();
    }

    /**
     * @return Collection<int, Meeting>
     */
    #[Computed]
    public function meetings(): Collection
    {
        $month = $this->currentMonth();

        return Meeting::with(['attendees' => fn ($query) => $query->orderBy('name')])
            ->whereBetween('held_on', [$month->startOfMonth(), $month->endOfMonth()])
            ->orderBy('held_on')
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Filters on role names rather than `User::role()`, which throws when one of the roles
     * does not exist yet in the database.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        $memberRoles = array_map(fn (Role $role): string => $role->value, [Role::Parent, Role::Professeur]);

        return User::whereHas('roles', fn ($query) => $query->whereIn('name', $memberRoles))
            ->orderBy('name')
            ->get();
    }

    public function show(Meeting $meeting): void
    {
        $this->viewing = $meeting->load(['attendees' => fn ($query) => $query->orderBy('name')]);
        $this->showDetails = true;
    }

    public function create(?string $date = null): void
    {
        $this->authorizeManagement();

        $this->reset(['editing', 'viewing', 'showDetails', 'startsAt', 'title', 'notes', 'attendeeIds']);
        $this->heldOn = $date ?? now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(Meeting $meeting): void
    {
        $this->authorizeManagement();

        $this->reset(['viewing', 'showDetails']);
        $this->editing = $meeting;
        $this->heldOn = $meeting->held_on->format('Y-m-d');
        $this->startsAt = $meeting->starts_at ? substr($meeting->starts_at, 0, 5) : '';
        $this->title = $meeting->title;
        $this->notes = $meeting->notes ?? '';
        $this->attendeeIds = $meeting->attendees()->pluck('users.id')->all();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeManagement();

        $validated = $this->validate([
            'heldOn' => ['required', 'date_format:Y-m-d'],
            'startsAt' => ['nullable', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attendeeIds' => ['array'],
            'attendeeIds.*' => ['integer', 'in:'.$this->members->pluck('id')->implode(',')],
        ]);

        $attributes = [
            'held_on' => $validated['heldOn'],
            'starts_at' => $validated['startsAt'] ?: null,
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?: null,
        ];

        $meeting = $this->editing ?? new Meeting;
        $meeting->fill($attributes)->save();
        $meeting->attendees()->sync($validated['attendeeIds']);

        $this->month = $meeting->held_on->format('Y-m');
        $this->showModal = false;
    }

    public function delete(Meeting $meeting): void
    {
        $this->authorizeManagement();

        $meeting->delete();
        $this->reset(['viewing', 'showDetails']);
    }

    public function render()
    {
        return view('livewire.meetings');
    }

    /**
     * Falls back to the current month when the `month` query parameter is missing or malformed.
     */
    private function currentMonth(): CarbonImmutable
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->month)) {
            return CarbonImmutable::createFromFormat('!Y-m', $this->month);
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    private function authorizeManagement(): void
    {
        abort_unless($this->canManage, 403);
    }
}
