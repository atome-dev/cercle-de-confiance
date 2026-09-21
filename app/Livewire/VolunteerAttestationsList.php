<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Attestations de bénévolat')]
class VolunteerAttestationsList extends Component
{
    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        return User::role([Role::Parent, Role::Professeur, Role::Administrateur])
            ->with('volunteerAttestation')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.volunteer-attestations-list');
    }
}
