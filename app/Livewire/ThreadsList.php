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
        abort_unless(auth()->user()?->hasRole('membre'), 403);
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
            ->where(function ($q) use ($user) {
                $q->where('recipient_type', 'group')
                    ->orWhere('recipient_user_id', $user->id);
            })
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest('last_message_at')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.threads-list');
    }
}
