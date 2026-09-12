<?php

namespace App\Livewire;

use App\Models\Membre;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Nos membres')]
class Membres extends Component
{
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

    public function render()
    {
        return view('livewire.membres');
    }
}
