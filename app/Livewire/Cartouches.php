<?php

namespace App\Livewire;

use App\Enums\CartoucheIcone;
use App\Models\Cartouche;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
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

    public ?int $position = null;

    /**
     * @return Collection<int, Cartouche>
     */
    #[Computed]
    public function cartouches(): Collection
    {
        return Cartouche::orderBy('position')->orderBy('id')->get();
    }

    public function create(): void
    {
        $this->reset(['editing', 'icone', 'titre', 'description']);
        $this->position = (int) Cartouche::max('position') + 1;
        $this->showModal = true;
    }

    public function edit(Cartouche $cartouche): void
    {
        $this->editing = $cartouche;
        $this->icone = $cartouche->icone->value;
        $this->titre = $cartouche->titre;
        $this->description = $cartouche->description;
        $this->position = $cartouche->position;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'icone' => ['required', Rule::enum(CartoucheIcone::class)],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'position' => ['required', 'integer', 'min:1'],
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
