<div>
    @php
        $currentMonth = \Carbon\CarbonImmutable::createFromFormat('!Y-m', $month)->locale('fr');
        $meetingsByDay = $this->meetings->groupBy(fn ($meeting) => $meeting->held_on->format('Y-m-d'));
    @endphp

    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Réunions') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Calendrier des réunions du cercle et des membres présents.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            {{-- Month navigation --}}
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <flux:button icon="chevron-left" variant="ghost" wire:click="previousMonth" :aria-label="__('Mois précédent')" data-test="previous-month" />
                    <h2 class="min-w-48 text-center font-display text-2xl capitalize text-text" data-test="current-month">
                        {{ $currentMonth->translatedFormat('F Y') }}
                    </h2>
                    <flux:button icon="chevron-right" variant="ghost" wire:click="nextMonth" :aria-label="__('Mois suivant')" data-test="next-month" />
                    <flux:button size="sm" wire:click="today">{{ __("Aujourd'hui") }}</flux:button>
                </div>

                @if ($this->canManage)
                    <flux:button variant="primary" icon="plus" wire:click="create">
                        {{ __('Ajouter une réunion') }}
                    </flux:button>
                @endif
            </div>

            {{-- Calendar grid --}}
            <div class="overflow-hidden rounded-lg border border-border">
                <div class="grid grid-cols-7 border-b border-border bg-surface-muted text-center text-sm font-medium text-text-muted">
                    @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $weekday)
                        <div class="py-2">{{ $weekday }}</div>
                    @endforeach
                </div>

                @foreach ($this->weeks as $week)
                    <div class="grid grid-cols-7 border-b border-border last:border-b-0" wire:key="week-{{ $week[0]->format('Y-m-d') }}">
                        @foreach ($week as $day)
                            @php
                                $dayKey = $day->format('Y-m-d');
                                $isCurrentMonth = $day->format('Y-m') === $month;
                            @endphp
                            <div
                                wire:key="day-{{ $dayKey }}"
                                @class([
                                    'group relative min-h-16 border-e border-border p-1 last:border-e-0 sm:min-h-28 sm:p-2',
                                    'bg-surface' => $isCurrentMonth,
                                    'bg-surface-muted/50 text-text-muted' => ! $isCurrentMonth,
                                ])
                            >
                                <div class="flex items-center justify-between">
                                    <span @class([
                                        'inline-flex size-7 items-center justify-center rounded-full text-sm',
                                        'bg-primary-500 font-semibold text-white' => $day->isToday(),
                                        'text-text-muted opacity-60' => ! $isCurrentMonth && ! $day->isToday(),
                                    ])>
                                        {{ $day->day }}
                                    </span>

                                    @if ($this->canManage && $isCurrentMonth)
                                        <button
                                            type="button"
                                            wire:click="create('{{ $dayKey }}')"
                                            class="hidden rounded p-1 text-text-muted opacity-0 transition hover:text-primary-500 group-hover:opacity-100 sm:block"
                                            title="{{ __('Ajouter une réunion le :date', ['date' => $day->locale('fr')->translatedFormat('j F')]) }}"
                                        >
                                            <flux:icon name="plus" class="size-4" />
                                        </button>
                                    @endif
                                </div>

                                @if ($isCurrentMonth)
                                    <div class="mt-1 space-y-1">
                                        @foreach ($meetingsByDay->get($dayKey, []) as $meeting)
                                            <button
                                                type="button"
                                                wire:key="meeting-chip-{{ $meeting->id }}"
                                                wire:click="show({{ $meeting->id }})"
                                                class="block w-full truncate rounded bg-primary-50 px-1.5 py-0.5 text-start text-xs font-medium text-primary-700 transition hover:bg-primary-100"
                                                title="{{ $meeting->title }}"
                                            >
                                                <span class="sm:hidden">•</span>
                                                <span class="hidden sm:inline">
                                                    @if ($meeting->starts_at)
                                                        {{ substr($meeting->starts_at, 0, 5) }}
                                                    @endif
                                                    {{ $meeting->title }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- Month list (readable on small screens) --}}
            <div class="mt-10">
                <h3 class="mb-4 font-display text-xl text-text">{{ __('Réunions du mois') }}</h3>

                @forelse ($this->meetings as $meeting)
                    <div wire:key="meeting-{{ $meeting->id }}" class="mb-3 rounded-lg border border-border p-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <button type="button" wire:click="show({{ $meeting->id }})" class="text-start">
                                <div class="text-sm capitalize text-text-muted">
                                    {{ $meeting->held_on->locale('fr')->translatedFormat('l j F Y') }}
                                    @if ($meeting->starts_at)
                                        · {{ substr($meeting->starts_at, 0, 5) }}
                                    @endif
                                </div>
                                <div class="font-medium text-text hover:text-primary-500">{{ $meeting->title }}</div>
                            </button>

                            @if ($this->canManage)
                                <div class="flex gap-2">
                                    <flux:button size="sm" wire:click="edit({{ $meeting->id }})">{{ __('Modifier') }}</flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        wire:click="delete({{ $meeting->id }})"
                                        wire:confirm="{{ __('Supprimer cette réunion ?') }}"
                                    >
                                        {{ __('Supprimer') }}
                                    </flux:button>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2 text-sm text-text-muted">
                            <span class="font-medium">{{ __('Présents :') }}</span>
                            @if ($meeting->attendees->isEmpty())
                                <span class="italic">{{ __('aucun membre indiqué') }}</span>
                            @else
                                {{ $meeting->attendees->pluck('name')->join(', ') }}
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-text-muted">{{ __('Aucune réunion ce mois-ci.') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Details modal --}}
    <flux:modal wire:model.self="showDetails" class="md:w-[28rem]">
        @if ($viewing)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ $viewing->title }}</flux:heading>
                    <flux:text class="mt-1 capitalize">
                        {{ $viewing->held_on->locale('fr')->translatedFormat('l j F Y') }}
                        @if ($viewing->starts_at)
                            · {{ substr($viewing->starts_at, 0, 5) }}
                        @endif
                    </flux:text>
                </div>

                @if ($viewing->notes)
                    <flux:text class="whitespace-pre-line">{{ $viewing->notes }}</flux:text>
                @endif

                <div>
                    <flux:heading>{{ __('Membres présents') }} ({{ $viewing->attendees->count() }})</flux:heading>
                    @if ($viewing->attendees->isEmpty())
                        <flux:text class="mt-2 italic">{{ __('Aucun membre indiqué.') }}</flux:text>
                    @else
                        <ul class="mt-2 space-y-2">
                            @foreach ($viewing->attendees as $attendee)
                                <li class="flex items-center gap-2">
                                    <flux:avatar :name="$attendee->name" :src="$attendee->photo_url" size="xs" circle />
                                    <span class="text-sm text-text">{{ $attendee->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    @if ($this->canManage)
                        <flux:button size="sm" wire:click="edit({{ $viewing->id }})">{{ __('Modifier') }}</flux:button>
                    @endif
                    <flux:modal.close>
                        <flux:button size="sm" variant="ghost">{{ __('Fermer') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Create / edit modal --}}
    @if ($this->canManage)
        <flux:modal wire:model.self="showModal" class="md:w-[32rem]">
            <form wire:submit="save" class="space-y-6">
                <flux:heading size="lg">
                    {{ $editing ? __('Modifier la réunion') : __('Ajouter une réunion') }}
                </flux:heading>

                <flux:input :label="__('Titre')" wire:model="title" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="date" :label="__('Date')" wire:model="heldOn" />
                    <flux:input type="time" :label="__('Heure (facultatif)')" wire:model="startsAt" />
                </div>

                <flux:textarea :label="__('Notes (facultatif)')" wire:model="notes" rows="3" />

                <flux:checkbox.group wire:model="attendeeIds" :label="__('Membres présents')" class="max-h-60 overflow-y-auto">
                    @foreach ($this->members as $member)
                        <flux:checkbox :value="$member->id" :label="$member->name" wire:key="attendee-{{ $member->id }}" />
                    @endforeach
                </flux:checkbox.group>

                <div class="flex">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Annuler') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Enregistrer') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
