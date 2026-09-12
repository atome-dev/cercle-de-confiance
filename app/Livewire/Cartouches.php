<?php

namespace App\Livewire;

use App\Models\Cartouche;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Nos cartouches')]
class Cartouches extends Component
{
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

    public function render()
    {
        return view('livewire.cartouches');
    }
}
