<div>
    @php
        $days = $this->days;
        $modesBySlot = $this->modesBySlot;
        $times = \App\Models\MeetingAvailability::slotTimes();
        $modeClasses = [
            'presentiel' => 'bg-sage-400 hover:bg-sage-500',
            'distanciel' => 'bg-secondary-300 hover:bg-secondary-400',
        ];
    @endphp

    {{-- Week navigation --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
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

        @if ($this->canEdit)
            <flux:button
                size="sm"
                icon="document-duplicate"
                wire:click="copyPreviousWeek"
                wire:confirm="{{ __('Remplacer les disponibilités à venir de cette semaine par celles de la semaine précédente ?') }}"
            >
                {{ __('Cloner la semaine précédente') }}
            </flux:button>
        @endif
    </div>

    @if (! $this->canEdit)
        <flux:callout icon="information-circle" class="mb-6">
            <flux:callout.text>{{ __('Seuls les parents et professeurs, qui peuvent participer aux réunions, renseignent leurs disponibilités.') }}</flux:callout.text>
        </flux:callout>
    @else
        <div
            x-data="{
                brush: 'presentiel',
                painting: false,
                touch: false,
                pending: [],
                anchor: null,
                begin(event) {
                    const cell = event.target.closest('[data-slot]');
                    if (! cell || cell.disabled) return;
                    this.touch = event.pointerType !== 'mouse';
                    if (! this.touch) event.preventDefault();
                    this.painting = true;
                    this.anchor = cell;
                    this.pending = [cell.dataset.slot];
                },
                {{-- Selects the whole rectangle between the first cell and the one under the pointer,
                     so cells skipped by a fast mouse move are still included. --}}
                extend(event) {
                    if (! this.painting || this.touch) return;
                    const cell = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-slot]');
                    if (! cell || ! this.$root.contains(cell)) return;
                    const [firstColumn, lastColumn] = [+this.anchor.dataset.column, +cell.dataset.column].sort((a, b) => a - b);
                    const [firstRow, lastRow] = [+this.anchor.dataset.row, +cell.dataset.row].sort((a, b) => a - b);
                    this.pending = [...this.$root.querySelectorAll('[data-slot]')]
                        .filter((slot) => ! slot.disabled
                            && +slot.dataset.column >= firstColumn && +slot.dataset.column <= lastColumn
                            && +slot.dataset.row >= firstRow && +slot.dataset.row <= lastRow)
                        .map((slot) => slot.dataset.slot);
                },
                commit() {
                    if (! this.painting) return;
                    this.painting = false;
                    const slots = this.pending;
                    $wire.paint(slots, this.brush).then(() => {
                        if (this.pending === slots) this.pending = [];
                    });
                },
                cancel() {
                    this.painting = false;
                    this.pending = [];
                },
            }"
            @pointerup.window="commit()"
            @pointercancel.window="cancel()"
        >
            {{-- Brush --}}
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <span class="text-sm text-text-muted">{{ __('Cliquez ou faites glisser sur la grille pour indiquer :') }}</span>
                <div class="inline-flex rounded-lg bg-surface-muted p-1" role="radiogroup" aria-label="{{ __('Disponibilité à appliquer') }}">
                    @foreach ([
                        'presentiel' => [__('Présentiel'), 'bg-sage-400'],
                        'distanciel' => [__('Distanciel'), 'bg-secondary-300'],
                        'indisponible' => [__('Indisponible'), 'bg-surface border border-border'],
                    ] as $mode => [$label, $swatch])
                        <button
                            type="button"
                            role="radio"
                            :aria-checked="brush === '{{ $mode }}'"
                            @click="brush = '{{ $mode }}'"
                            :class="brush === '{{ $mode }}' ? 'bg-surface shadow-xs text-text' : 'text-text-muted hover:text-text'"
                            class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition"
                            data-test="brush-{{ $mode }}"
                        >
                            <span class="size-3 rounded-sm {{ $swatch }}"></span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Weekly grid --}}
            <div class="overflow-x-auto rounded-lg border border-border">
                <table class="w-full min-w-[36rem] table-fixed border-collapse select-none text-xs">
                    <thead>
                        <tr class="bg-surface-muted text-text-muted">
                            <th class="w-14"></th>
                            @foreach ($days as $day)
                                @php
                                    $daySlots = collect($times)
                                        ->map(fn ($time) => $day->format('Y-m-d').' '.$time)
                                        ->filter(fn ($slot) => \Carbon\CarbonImmutable::createFromFormat('!Y-m-d H:i', $slot)->isFuture())
                                        ->values();
                                @endphp
                                <th class="py-2 font-medium" wire:key="day-header-{{ $day->format('Y-m-d') }}">
                                    @if ($daySlots->isNotEmpty())
                                        <button
                                            type="button"
                                            class="w-full rounded capitalize transition hover:text-primary-500"
                                            @click="$wire.paint(@js($daySlots), brush)"
                                            title="{{ __('Appliquer à toute la journée') }}"
                                        >
                                            {{ $day->locale('fr')->translatedFormat('D j') }}
                                        </button>
                                    @else
                                        <span class="capitalize opacity-60">{{ $day->locale('fr')->translatedFormat('D j') }}</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody @pointerdown="begin($event)" @pointermove="extend($event)">
                        @foreach ($times as $row => $time)
                            <tr wire:key="row-{{ $time }}" @class(['border-t border-border/60' => str_ends_with($time, ':00')])>
                                <th class="pe-2 text-end align-top font-normal text-text-muted">
                                    @if (str_ends_with($time, ':00'))
                                        <span class="relative -top-2">{{ $time }}</span>
                                    @endif
                                </th>
                                @foreach ($days as $column => $day)
                                    @php
                                        $key = $day->format('Y-m-d').' '.$time;
                                        $mode = $modesBySlot[$key] ?? null;
                                        $isPast = ! \Carbon\CarbonImmutable::createFromFormat('!Y-m-d H:i', $key)->isFuture();
                                    @endphp
                                    <td class="border-s border-border/60 p-0" wire:key="slot-{{ $key }}">
                                        <button
                                            type="button"
                                            data-slot="{{ $key }}" data-mode="{{ $mode ?? 'indisponible' }}"
                                            data-column="{{ $column }}"
                                            data-row="{{ $row }}"
                                            @disabled($isPast)
                                            title="{{ $day->locale('fr')->translatedFormat('l j F') }} · {{ $time }}"
                                            @class([
                                                'relative block h-5 w-full transition',
                                                $modeClasses[$mode] ?? 'bg-surface hover:bg-surface-muted' => ! $isPast,
                                                'cursor-not-allowed bg-surface-muted/60' => $isPast && ! $mode,
                                                ($mode === 'presentiel' ? 'bg-sage-400/40' : 'bg-secondary-300/40') => $isPast && $mode,
                                            ])
                                        >
                                            <span
                                                x-show="pending.includes('{{ $key }}')"
                                                x-cloak
                                                class="absolute inset-0 ring-1 ring-inset ring-primary-500"
                                                :class="{ 'bg-sage-400': brush === 'presentiel', 'bg-secondary-300': brush === 'distanciel', 'bg-surface': brush === 'indisponible' }"
                                            ></span>
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-3 text-sm text-text-muted">
                {{ __('Les créneaux sont de 30 minutes, de 8 h à 22 h. Un créneau laissé blanc signifie « indisponible ». Les créneaux passés ne sont plus modifiables.') }}
            </p>
        </div>
    @endif
</div>
