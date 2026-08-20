<?php

use App\Models\Membre;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Nos membres')] class extends Component {
    public bool $showModal = false;

    public ?Membre $editing = null;

    public string $nom = '';

    public string $titre = '';

    public string $role = 'parent';

    public ?string $photo = null;

    public ?string $courriel = null;

    /**
     * @return Collection<int, Membre>
     */
    #[Computed]
    public function membres(): Collection
    {
        return Membre::orderBy('nom')->get();
    }

    public function create(): void
    {
        $this->reset(['editing', 'nom', 'titre', 'role', 'photo', 'courriel']);
        $this->role = 'parent';
        $this->showModal = true;
    }

    public function edit(Membre $membre): void
    {
        $this->editing = $membre;
        $this->nom = $membre->nom;
        $this->titre = $membre->titre;
        $this->role = $membre->role;
        $this->photo = $membre->photo;
        $this->courriel = $membre->courriel;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'nom' => ['required', 'string', 'max:255'],
            'titre' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:parent,professeur,salarie'],
            'photo' => ['nullable', 'string', 'max:255'],
            'courriel' => ['nullable', 'email', 'max:255'],
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Membre::create($validated);
        }

        $this->showModal = false;
    }

    public function delete(Membre $membre): void
    {
        $membre->delete();
    }

    /**
     * Tailwind classes are written as full literal strings (not interpolated)
     * so the JIT scanner can pick them up statically.
     */
    public function avatarClasses(string $role): string
    {
        return match ($role) {
            'parent' => '!bg-role-parent !text-white',
            'professeur' => '!bg-role-professeur !text-white',
            'salarie' => '!bg-role-salarie !text-white',
            default => '',
        };
    }

    public function badgeClasses(string $role): string
    {
        return match ($role) {
            'parent' => 'bg-role-parent/10 text-role-parent',
            'professeur' => 'bg-role-professeur/10 text-role-professeur',
            'salarie' => 'bg-role-salarie/10 text-role-salarie',
            default => 'bg-surface-muted text-text-muted',
        };
    }
}; ?>

<div>
    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Nos membres') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Découvrez les personnes qui composent le Cercle de Confiance.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Members table --}}
    <section class="py-24">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mb-8 flex justify-end">
                <flux:button variant="primary" wire:click="create">
                    {{ __('Ajouter un membre') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nom') }}</flux:table.column>
                    <flux:table.column>{{ __('Titre') }}</flux:table.column>
                    <flux:table.column>{{ __('Rôle') }}</flux:table.column>
                    <flux:table.column>{{ __('Courriel') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->membres as $membre)
                        <flux:table.row wire:key="membre-{{ $membre->id }}">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar
                                    :name="$membre->nom"
                                    :src="$membre->photo_url"
                                    size="xl"
                                    circle
                                    class="{{ $this->avatarClasses($membre->role) }}"
                                />
                                {{ $membre->nom }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $membre->titre }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase {{ $this->badgeClasses($membre->role) }}">
                                    {{ $membre->role }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $membre->courriel }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" wire:click="edit({{ $membre->id }})">
                                        {{ __('Modifier') }}
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        wire:click="delete({{ $membre->id }})"
                                        wire:confirm="{{ __('Supprimer ce membre ?') }}"
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
                    {{ $editing ? __('Modifier le membre') : __('Ajouter un membre') }}
                </flux:heading>
            </div>

            <flux:input :label="__('Nom')" wire:model="nom" />
            <flux:input :label="__('Titre')" wire:model="titre" />

            <flux:select :label="__('Rôle')" wire:model="role">
                <flux:select.option value="parent">{{ __("Parent d'élève") }}</flux:select.option>
                <flux:select.option value="professeur">{{ __('Professeur') }}</flux:select.option>
                <flux:select.option value="salarie">{{ __('Salarié') }}</flux:select.option>
            </flux:select>

            <flux:input :label="__('Photo')" wire:model="photo" :description="__('Nom du fichier dans storage/membres, exemple : nom-prenom.jpg')" />
            <flux:input :label="__('Courriel')" type="email" wire:model="courriel" />

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
