<div>
    @php
        $days = $this->days;
        $times = \App\Models\MeetingAvailability::slotTimes();
        $candidates = $this->candidates;
        $memberCount = $this->members->count();
        $maxTotal = max(1, collect($candidates)->max('total') ?? 0);
        $selectedSlot = $selected ? ($candidates[$selected] ?? null) : null;
        $missing = $this->members->whereNotIn('id', $this->respondentIds);
    @endphp

    {{-- Week navigation --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <flux:button icon="chevron-left" variant="ghost" wire:click="previousWeek" :aria-label="__('Semaine précédente')" data-test="previous-week" />
        <h2 class="min-w-48 text-center font-display text-xl text-text" data-test="current-week">
            {{ __('Semaine du :start au :end', [
                'start' => $days[0]->locale('fr')->translatedFormat('j F'),
                'end' => $days[6]->locale('fr')->translatedFormat('j F Y'),
            ]) }}
        </h2>
        <flux:button icon="chevron-right" variant="ghost" wire:click="nextWeek" :aria-label="__('Semaine suivante')" data-test="next-week" />
        <flux:button size="sm" wire:click="thisWeek">{{ __('Cette semaine') }}</flux:button>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <flux:select wire:model.live="duration" :label="__('Durée de la réunion')" data-test="filter-duration">
            @for ($minutes = \App\Models\MeetingAvailability::SLOT_MINUTES; $minutes <= \App\Livewire\MeetingSlotFinder::MAX_DURATION; $minutes += \App\Models\MeetingAvailability::SLOT_MINUTES)
                <flux:select.option :value="$minutes">
                    {{ $minutes < 60 ? $minutes.' min' : intdiv($minutes, 60).' h'.($minutes % 60 ? ' '.($minutes % 60) : '') }}
                </flux:select.option>
            @endfor
        </flux:select>

        <flux:select wire:model.live="minAttendees" :label="__('Personnes au minimum')" data-test="filter-min-attendees">
            @foreach (range(1, max(1, $memberCount)) as $count)
                <flux:select.option :value="$count">{{ $count }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="minInPerson" :label="__('Dont en présentiel au minimum')" data-test="filter-min-in-person">
            @foreach (range(0, max(1, $memberCount)) as $count)
                <flux:select.option :value="$count">{{ $count }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <p class="mb-8 text-sm text-text-muted" data-test="respondents">
        {{ __(':count membre(s) sur :total ont indiqué leurs disponibilités cette semaine.', ['count' => count($this->respondentIds), 'total' => $memberCount]) }}
        @if ($missing->isNotEmpty())
            <span>{{ __('Pas encore renseigné :') }} {{ $missing->pluck('name')->join(', ') }}.</span>
        @endif
    </p>

    {{-- Best slots --}}
    <h3 class="mb-3 font-display text-xl text-text">{{ __('Meilleurs créneaux') }}</h3>

    @forelse ($this->bestSlots as $slot)
        @php $key = $slot['start']->format('Y-m-d H:i'); @endphp
        <div wire:key="best-{{ $key }}" class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-4" data-test="best-slot">
            <button type="button" wire:click="select('{{ $key }}')" class="text-start">
                <div class="font-medium capitalize text-text hover:text-primary-500">
                    {{ $slot['start']->locale('fr')->translatedFormat('l j F') }}
                    · {{ $slot['start']->format('H:i') }} – {{ $slot['start']->addMinutes($duration)->format('H:i') }}
                </div>
                <div class="text-sm text-text-muted">
                    {{ trans_choice(':count personne|:count personnes', $slot['total']) }}
                    ({{ $slot['inPerson']->count() }} {{ __('en présentiel') }}, {{ $slot['remote']->count() }} {{ __('à distance') }})
                </div>
            </button>
            <flux:button size="sm" variant="primary" icon="calendar" wire:click="plan('{{ $key }}')">
                {{ __('Planifier') }}
            </flux:button>
        </div>
    @empty
        <p class="mb-3 text-text-muted" data-test="no-slot">{{ __('Aucun créneau ne correspond à ces critères cette semaine.') }}</p>
    @endforelse

    {{-- Heatmap --}}
    <h3 class="mb-1 mt-10 font-display text-xl text-text">{{ __('Vue de la semaine') }}</h3>
    <p class="mb-4 text-sm text-text-muted">
        {{ __('Chaque case indique combien de personnes sont disponibles pour une réunion commençant à cette heure. Les cases grisées ne respectent pas les critères.') }}
    </p>

    <div class="overflow-x-auto rounded-lg border border-border">
        <table class="w-full min-w-[36rem] table-fixed border-collapse text-xs">
            <thead>
                <tr class="bg-surface-muted text-text-muted">
                    <th class="w-14"></th>
                    @foreach ($days as $day)
                        <th class="py-2 font-medium capitalize" wire:key="finder-day-{{ $day->format('Y-m-d') }}">
                            {{ $day->locale('fr')->translatedFormat('D j') }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($times as $time)
                    <tr wire:key="finder-row-{{ $time }}" @class(['border-t border-border/60' => str_ends_with($time, ':00')])>
                        <th class="pe-2 text-end align-top font-normal text-text-muted">
                            @if (str_ends_with($time, ':00'))
                                <span class="relative -top-2">{{ $time }}</span>
                            @endif
                        </th>
                        @foreach ($days as $day)
                            @php
                                $key = $day->format('Y-m-d').' '.$time;
                                $candidate = $candidates[$key] ?? null;
                                $ratio = $candidate ? $candidate['total'] / $maxTotal : 0;
                            @endphp
                            <td class="border-s border-border/60 p-0" wire:key="finder-{{ $key }}">
                                @if ($candidate && $candidate['total'] > 0)
                                    <button
                                        type="button"
                                        wire:click="select('{{ $key }}')"
                                        title="{{ trans_choice(':count personne|:count personnes', $candidate['total']) }}, {{ $candidate['inPerson']->count() }} {{ __('en présentiel') }}"
                                        @class([
                                            'block h-5 w-full font-medium transition',
                                            'ring-2 ring-inset ring-secondary-400' => $selected === $key,
                                            'bg-surface-muted text-text-muted/60' => ! $candidate['matches'],
                                            'bg-primary-100 text-primary-700 hover:bg-primary-200' => $candidate['matches'] && $ratio < 0.5,
                                            'bg-primary-300 text-primary-900 hover:bg-primary-400' => $candidate['matches'] && $ratio >= 0.5 && $ratio < 1,
                                            'bg-primary-500 text-white hover:bg-primary-600' => $candidate['matches'] && $ratio >= 1,
                                        ])
                                    >
                                        {{ $candidate['total'] }}@if ($candidate['inPerson']->isNotEmpty())<span class="opacity-70"> ({{ $candidate['inPerson']->count() }})</span>@endif
                                    </button>
                                @else
                                    <div class="h-5 bg-surface"></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-xs text-text-muted">{{ __('Entre parenthèses : nombre de personnes en présentiel.') }}</p>

    {{-- Selected slot details --}}
    <flux:modal wire:model.self="showDetails" class="md:w-[28rem]">
        @if ($selectedSlot)
            <div class="space-y-4" data-test="slot-details">
                <div>
                    <flux:heading size="lg" class="capitalize">
                        {{ $selectedSlot['start']->locale('fr')->translatedFormat('l j F') }}
                    </flux:heading>
                    <flux:text class="mt-1">
                        {{ $selectedSlot['start']->format('H:i') }} – {{ $selectedSlot['start']->addMinutes($duration)->format('H:i') }}
                    </flux:text>
                </div>

                @foreach ([
                    [__('En présentiel'), $selectedSlot['inPerson']],
                    [__('À distance'), $selectedSlot['remote']],
                ] as [$label, $people])
                    <div>
                        <flux:heading>{{ $label }} ({{ $people->count() }})</flux:heading>
                        @if ($people->isEmpty())
                            <flux:text class="mt-2 italic">{{ __('Personne.') }}</flux:text>
                        @else
                            <ul class="mt-2 space-y-2">
                                @foreach ($people as $person)
                                    <li class="flex items-center gap-2" wire:key="slot-person-{{ $person->id }}">
                                        <flux:avatar :name="$person->name" :src="$person->photo_url" size="xs" circle />
                                        <span class="text-sm text-text">{{ $person->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button size="sm" variant="ghost">{{ __('Fermer') }}</flux:button>
                    </flux:modal.close>
                    <flux:button size="sm" variant="primary" icon="calendar" wire:click="plan('{{ $selected }}')">
                        {{ __('Planifier') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
