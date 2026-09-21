<div>
    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Attestations de bénévolat') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Suivi des attestations remplies par les membres.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="py-24">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nom') }}</flux:table.column>
                    <flux:table.column>{{ __('Rôle') }}</flux:table.column>
                    <flux:table.column>{{ __('Statut') }}</flux:table.column>
                    <flux:table.column>{{ __('Action') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->members as $member)
                        <flux:table.row wire:key="member-{{ $member->id }}">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$member->name" size="sm" circle />
                                {{ $member->name }}
                            </flux:table.cell>
                            <flux:table.cell>{{ ucfirst($member->getRoleNames()->first() ?? '—') }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($member->volunteerAttestation)
                                    <flux:badge color="green" size="sm">
                                        {{ __('Soumise le :date', ['date' => $member->volunteerAttestation->submitted_at->format('d/m/Y')]) }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('En attente') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($member->volunteerAttestation)
                                    <flux:button
                                        size="sm"
                                        icon="arrow-down-tray"
                                        href="{{ route('attestations-benevolat.download', $member->volunteerAttestation) }}"
                                    >
                                        {{ __('Télécharger') }}
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </section>
</div>
