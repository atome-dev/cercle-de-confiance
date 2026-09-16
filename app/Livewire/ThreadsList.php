<?php

namespace App\Livewire;

use App\Models\Thread;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::public')]
#[Title('Mes dossiers')]
class ThreadsList extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['administrateur', 'parent', 'professeur']), 403);
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function threads()
    {
        $user = auth()->user();

        return Thread::query()
            ->whereHas('grants', fn ($q) => $q->where('user_id', $user->id))
            ->with([
                // Contraints aux données nécessaires à hasUnreadFor() pour chaque
                // dossier de la page, en 2 requêtes au lieu d'un aller-retour par
                // dossier (N+1) : la lecture de l'utilisateur courant, et les
                // messages avec juste les colonnes utiles à la comparaison.
                'reads' => fn ($q) => $q->where('reader_user_id', $user->id),
                'messages' => fn ($q) => $q->select(['id', 'thread_id', 'author_type', 'author_user_id', 'created_at']),
            ])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest('last_message_at')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.threads-list');
    }
}
