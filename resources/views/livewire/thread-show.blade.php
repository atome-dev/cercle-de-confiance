<div class="max-w-3xl mx-auto p-6">
    @if ($accessDeniedReason)
        <flux:callout variant="danger" icon="lock-closed" :heading="$accessDeniedReason" />
    @else
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">Dossier {{ $thread->code }}</flux:heading>
                <flux:text class="text-gray-500">
                    {{ $thread->isForGroup() ? 'Adressé à la commission' : 'Adressé à ' . $thread->recipientUser?->name }}
                </flux:text>
            </div>

            <flux:badge :variant="match($thread->status) {
                'nouveau' => 'blue',
                'en_cours' => 'amber',
                'archive' => 'gray',
            }">
                {{ str($thread->status)->replace('_', ' ')->title() }}
            </flux:badge>
        </div>

        @auth
            @if ($thread->isAccessibleByMember(auth()->user()))
                <div class="flex gap-2 mb-6">
                    <flux:button size="sm" wire:click="updateStatus('en_cours')">En cours</flux:button>
                    <flux:button size="sm" wire:click="updateStatus('archive')">Archiver</flux:button>
                </div>
            @endif
        @endauth

        <div class="space-y-4 mb-8">
            @foreach ($this->decryptedMessages as $msg)
                <div class="flex flex-col {{ $msg['author_type'] === 'member' ? 'items-end' : 'items-start' }}">
                    <div class="max-w-lg rounded-lg px-4 py-3 {{ $msg['author_type'] === 'member' ? 'bg-blue-50' : 'bg-gray-100' }}">
                        <flux:text class="text-xs text-gray-500 mb-1">
                            {{ $msg['author_type'] === 'member' ? ($msg['author_name'] ?? 'Membre') : 'Expéditeur' }}
                            · {{ $msg['created_at']->format('d/m/Y H:i') }}
                        </flux:text>
                        <p class="whitespace-pre-line">{{ $msg['plaintext'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="reply" class="space-y-4">
            <flux:textarea wire:model="newMessage" label="Votre réponse" rows="4" />
            <flux:button type="submit" variant="primary" icon="paper-airplane">Répondre</flux:button>
        </form>
    @endif
</div>
