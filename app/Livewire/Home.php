<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Cartouche;
use App\Models\User;
// use App\Models\Membre;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Accueil')]
class Home extends Component
{
    /**
     * @var array<int, array{nom: string, titre: string, role: string, photo: ?string, photo_url: ?string}>
     */
    public array $members;

    /**
     * @var array<int, array{icone: string, titre: string, description: string}>
     */
    public array $features;

    public function mount(): void
    {
        $this->members = User::role([Role::Parent, Role::Professeur])
            ->orderBy('name')
            ->get()
            ->toArray();

        ray($this->members);

        $this->features = Cartouche::orderBy('id')
            ->get()
            ->toArray();
    }

    /**
     * Tailwind classes are written as full literal strings (not interpolated)
     * so the JIT scanner can pick them up statically.
     */
    public function avatarClasses(?string $role): string
    {
        return match ($role) {
            'parent' => '!bg-role-parent !text-white',
            'professeur' => '!bg-role-professeur !text-white',
            'salarie' => '!bg-role-salarie !text-white',
            default => '',
        };
    }

    public function badgeClasses(?string $role): string
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
        return view('livewire.home');
    }
}
