<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\User;
use App\Models\VolunteerAttestation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Remove the attestation and its PDF so the member can submit a new one.
     */
    public function delete(VolunteerAttestation $attestation): void
    {
        Gate::authorize('delete', $attestation);

        Storage::disk('local')->delete($attestation->disk_path);
        $attestation->delete();

        unset($this->members);
    }

    public function render()
    {
        return view('livewire.volunteer-attestations-list');
    }
}
