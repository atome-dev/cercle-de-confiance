<?php

namespace App\Livewire;

use App\Actions\ReplyToThread;
use App\Actions\ShareThread;
use App\Enums\Role;
use App\Enums\SchoolClass;
use App\Enums\Section;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;
use Illuminate\Validation\Rule;
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

    public array $shareUserIds = [];

    public ?string $classificationSection = null;

    public ?string $classificationSchoolClass = null;

    public function mount(Thread $thread): void
    {
        $this->thread = $thread;
        $this->classificationSection = $thread->section?->value;
        $this->classificationSchoolClass = $thread->school_class?->value;

        if (! $this->resolveThreadKey()) {
            $this->accessDeniedReason = 'Vous n\'avez pas accès à ce dossier.';

            return;
        }

        $this->thread->markReadFor(auth()->user());
    }

    /**
     * Recomputed on every request — private/protected properties don't
     * survive Livewire's hydrate/dehydrate cycle between requests, so this
     * cannot be cached on the component instance across calls.
     */
    private function resolveThreadKey(): ?string
    {
        if (auth()->check() && $this->thread->isAccessibleBy(auth()->user())) {
            return base64_encode($this->thread->decryptKeyFor(auth()->user()));
        }

        $privateKey = session("anon_access_{$this->thread->id}");

        if ($this->thread->is_anonymous && $privateKey) {
            try {
                return base64_encode($this->thread->decryptKeyForAnonCode($privateKey));
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    #[Computed]
    public function decryptedMessages()
    {
        $resolvedThreadKey = $this->resolveThreadKey();

        if (! $resolvedThreadKey) {
            return collect();
        }

        $threadKey = base64_decode($resolvedThreadKey);
        $viewingAsSender = ! (auth()->check() && $this->thread->isAccessibleBy(auth()->user()));

        return $this->thread->messages->map(function ($message) use ($threadKey, $viewingAsSender) {
            return [
                'id' => $message->id,
                'author_type' => $message->author_type,
                'author_label' => $this->authorLabel($message, $viewingAsSender),
                'plaintext' => $message->decrypt($threadKey),
                'created_at' => $message->created_at,
            ];
        });
    }

    /**
     * "Vous" pour l'expéditeur qui consulte son propre dossier via le code de
     * suivi ; "Expéditeur" pour un membre qui lit les messages de ce même
     * expéditeur — la même ligne ne porte donc pas le même libellé selon qui
     * la regarde.
     */
    private function authorLabel(ThreadMessage $message, bool $viewingAsSender): string
    {
        if ($message->author_type === 'member') {
            return $message->author?->name ?? 'Membre';
        }

        return $viewingAsSender ? 'Vous' : 'Expéditeur';
    }

    public function reply(ReplyToThread $action): void
    {
        $this->validate([
            'newMessage' => 'required|string|min:1|max:5000',
        ]);

        $resolvedThreadKey = $this->resolveThreadKey();

        if (! $resolvedThreadKey) {
            $this->accessDeniedReason = 'Accès refusé.';

            return;
        }

        $hasGrant = auth()->check() && $this->thread->isAccessibleBy(auth()->user());

        $action->execute(
            thread: $this->thread,
            threadKey: base64_decode($resolvedThreadKey),
            message: $this->newMessage,
            authorType: $hasGrant ? 'member' : 'sender',
            authorUser: $hasGrant ? auth()->user() : null,
        );

        $this->newMessage = '';
        $this->thread->refresh();
        unset($this->decryptedMessages);
    }

    public function updatedClassificationSchoolClass(string $value): void
    {
        if ($value === '') {
            return;
        }

        $this->classificationSection = SchoolClass::from($value)->section()->value;
    }

    public function updatedClassificationSection(): void
    {
        $this->classificationSchoolClass = '';
    }

    public function updateStatus(string $status): void
    {
        if (! auth()->check() || ! $this->thread->isAccessibleBy(auth()->user())) {
            return;
        }

        $this->thread->update(['status' => $status]);
    }

    public function updateClassification(): void
    {
        if (! auth()->check() || ! $this->thread->isAccessibleBy(auth()->user())) {
            return;
        }

        $this->validate([
            'classificationSection' => ['nullable', Rule::enum(Section::class)],
            'classificationSchoolClass' => ['nullable', Rule::enum(SchoolClass::class)],
        ]);

        $this->thread->update([
            'section' => $this->classificationSection ?: null,
            'school_class' => $this->classificationSchoolClass ?: null,
        ]);
    }

    public function share(ShareThread $action): void
    {
        if (! auth()->check() || ! $this->thread->isAccessibleBy(auth()->user())) {
            return;
        }

        $action->execute($this->thread, auth()->user(), $this->shareUserIds);

        $this->shareUserIds = [];
        unset($this->grantees, $this->shareableUsers);
    }

    #[Computed]
    public function grantees()
    {
        return $this->thread->grants()->with(['user', 'grantedBy'])->get();
    }

    #[Computed]
    public function shareableUsers()
    {
        return User::role([Role::Parent, Role::Professeur, Role::Administrateur])
            ->whereNotIn('id', $this->thread->grants()->pluck('user_id'))
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.thread-show');
    }
}
