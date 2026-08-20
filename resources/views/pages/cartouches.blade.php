<?php

use App\Models\Cartouche;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Nos cartouches')] class extends Component {
    public bool $showModal = false;

    public ?Cartouche $editing = null;

    public string $icone = '';

    public string $titre = '';

    public string $description = '';

    /**
     * @return Collection<int, Cartouche>
     */
    #[Computed]
    public function cartouches(): Collection
    {
        return Cartouche::orderBy('titre')->get();
    }

    public function create(): void
    {
        $this->reset(['editing', 'icone', 'titre', 'description']);
        $this->showModal = true;
    }

    public function edit(Cartouche $cartouche): void
    {
        $this->editing = $cartouche;
        $this->icone = $cartouche->icone;
        $this->titre = $cartouche->titre;
        $this->description = $cartouche->description;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'icone' => ['required', 'string', 'max:255'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Cartouche::create($validated);
        }

        $this->showModal = false;
    }

    public function delete(Cartouche $cartouche): void
    {
        $cartouche->delete();
    }
}; ?>

<div>
    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Nos cartouches') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Gérez les cartouches affichées dans la section "Que faisons-nous ?".') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Cartouches table --}}
    <section class="py-24">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mb-8 flex justify-end">
                <flux:button variant="primary" wire:click="create">
                    {{ __('Ajouter une cartouche') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Icône') }}</flux:table.column>
                    <flux:table.column>{{ __('Titre') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->cartouches as $cartouche)
                        <flux:table.row wire:key="cartouche-{{ $cartouche->id }}">
                            <flux:table.cell class="text-2xl">{{ $cartouche->icone }}</flux:table.cell>
                            <flux:table.cell>{{ $cartouche->titre }}</flux:table.cell>
                            <flux:table.cell class="max-w-md truncate">{{ $cartouche->description }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" wire:click="edit({{ $cartouche->id }})">
                                        {{ __('Modifier') }}
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        wire:click="delete({{ $cartouche->id }})"
                                        wire:confirm="{{ __('Supprimer cette cartouche ?') }}"
                                    >
                                        {{ __('Supprimer') }}
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </section>

    {{-- Create / edit modal --}}
    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editing ? __('Modifier la cartouche') : __('Ajouter une cartouche') }}
                </flux:heading>
            </div>

            <flux:input :label="__('Icône')" wire:model="icone" :description="__('Un émoji, ex: 🤝')" />
            <flux:input :label="__('Titre')" wire:model="titre" />
            <flux:textarea :label="__('Description')" wire:model="description" rows="4" />

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Annuler') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Enregistrer') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
