<?php

namespace App\Livewire;

use App\Models\Thread;
use App\Services\ThreadCodeGenerator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Accéder à mon dossier')]
class AnonymousAccess extends Component
{
    public string $fullCode = '';

    public ?string $error = null;

    public function submit(ThreadCodeGenerator $generator)
    {
        $this->error = null;

        $parsed = $generator->parseFullCode($this->fullCode);

        if (! $parsed) {
            $this->error = 'Le format du code est invalide. Exemple : ABCD-EFGH';

            return;
        }

        [$threadCode, $privateKey] = $parsed;

        $thread = Thread::where('code', $threadCode)->first();

        if (! $thread || ! $thread->is_anonymous) {
            $this->error = 'Aucun dossier trouvé pour ce code.';

            return;
        }

        try {
            $thread->decryptKeyForAnonCode($privateKey);
        } catch (\Throwable) {
            $this->error = 'Code invalide.';

            return;
        }

        session(["anon_access_{$thread->id}" => $privateKey]);

        $this->redirect(route('threads.show', $thread), navigate: true);
    }

    public function render()
    {
        return view('livewire.anonymous-access');
    }
}
