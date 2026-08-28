<?php

namespace App\Livewire;

use App\Actions\ReplyToThread;
use App\Models\Thread;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Dossier')]
class ThreadShow extends Component
{
    public Thread $thread;

    public string $newMessage = '';

    public ?string $accessDeniedReason = null;

    private ?string $resolvedThreadKey = null;

    public function mount(Thread $thread): void
    {
        $this->thread = $thread;

        if (! $this->resolveThreadKey()) {
            $this->accessDeniedReason = 'Vous n\'avez pas accès à ce dossier.';

            return;
        }

        $this->thread->markReadFor(auth()->user());
    }

    private function resolveThreadKey(): bool
    {
        if (auth()->check() && $this->thread->isAccessibleByMember(auth()->user())) {
            $this->resolvedThreadKey = base64_encode($this->thread->decryptKeyForMember());

            return true;
        }

        if (auth()->check() && $this->thread->sender_user_id === auth()->id()) {
            $this->resolvedThreadKey = base64_encode($this->thread->decryptKeyForMember());

            return true;
        }

        $privateKey = session("anon_access_{$this->thread->id}");

        if ($this->thread->is_anonymous && $privateKey) {
            try {
                $this->resolvedThreadKey = base64_encode(
                    $this->thread->decryptKeyForAnonCode($privateKey)
                );

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    #[Computed]
    public function decryptedMessages()
    {
        if (! $this->resolvedThreadKey) {
            return collect();
        }

        $threadKey = base64_decode($this->resolvedThreadKey);

        return $this->thread->messages->map(function ($message) use ($threadKey) {
            return [
                'id' => $message->id,
                'author_type' => $message->author_type,
                'author_name' => $message->author?->name,
                'plaintext' => $message->decrypt($threadKey),
                'created_at' => $message->created_at,
            ];
        });
    }

    public function reply(ReplyToThread $action): void
    {
        $this->validate([
            'newMessage' => 'required|string|min:1|max:5000',
        ]);

        if (! $this->resolvedThreadKey) {
            $this->accessDeniedReason = 'Accès refusé.';

            return;
        }

        $isMember = auth()->check() && $this->thread->isAccessibleByMember(auth()->user());

        $action->execute(
            thread: $this->thread,
            threadKey: base64_decode($this->resolvedThreadKey),
            message: $this->newMessage,
            authorType: $isMember ? 'member' : 'sender',
            authorUser: $isMember ? auth()->user() : null,
        );

        $this->newMessage = '';
        $this->thread->refresh();
        unset($this->decryptedMessages);
    }

    public function updateStatus(string $status): void
    {
        if (! auth()->check() || ! $this->thread->isAccessibleByMember(auth()->user())) {
            return;
        }

        $this->thread->update(['status' => $status]);
    }

    public function render()
    {
        return view('livewire.thread-show');
    }
}
