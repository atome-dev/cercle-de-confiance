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
            @if ($thread->isAccessibleBy(auth()->user()))
                <div class="flex gap-2 mb-6">
                    <flux:button size="sm" wire:click="updateStatus('en_cours')">En cours</flux:button>
                    <flux:button size="sm" wire:click="updateStatus('archive')">Archiver</flux:button>
                </div>

                <flux:card class="mb-6 space-y-4">
                    <flux:heading size="sm">Partagé avec</flux:heading>

                    <ul class="space-y-1 text-sm text-gray-600">
                        @foreach ($this->grantees as $grant)
                            <li>
                                {{ $grant->user->name }}
                                —
                                {{ $grant->grantedBy ? 'partagé par '.$grant->grantedBy->name.' le '.$grant->created_at->format('d/m/Y') : 'accès automatique' }}
                            </li>
                        @endforeach
                    </ul>

                    <form wire:submit="share" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <flux:pillbox
                            wire:model="shareUserIds"
                            label="Partager avec"
                            placeholder="Choisir une ou plusieurs personnes…"
                            class="flex-1"
                        >
                            @foreach ($this->shareableUsers as $user)
                                <flux:pillbox.option value="{{ $user->id }}">{{ $user->name }}</flux:pillbox.option>
                            @endforeach
                        </flux:pillbox>

                        <flux:button type="submit" size="sm">Partager</flux:button>
                    </form>
                </flux:card>
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
