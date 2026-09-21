<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Attestation de bénévolat')]
class VolunteerAttestation extends Component
{
    public bool $showInfoModal = false;

    public function render()
    {
        return view('livewire.volunteer-attestation', [
            'submitted' => auth()->user()->volunteerAttestation,
        ]);
    }
}
