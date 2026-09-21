<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Éditeur PDF')]
class PdfEditor extends Component
{
    public bool $showInfoModal = false;

    public function render()
    {
        return view('livewire.pdf-editor');
    }
}
