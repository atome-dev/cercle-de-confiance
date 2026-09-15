<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Apparence')]
class Appearance extends Component
{
    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
