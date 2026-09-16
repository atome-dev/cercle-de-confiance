<div class="max-w-3xl mx-auto p-6">
    @if ($accessDeniedReason)
        <flux:callout variant="danger" icon="lock-closed" :heading="$accessDeniedReason" />
    @else
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">Dossier {{ $thread->code }}</flux:heading>
                @auth
                    @if ($thread->isAccessibleBy(auth()->user()))
                        <flux:text class="text-gray-500">
                            Expéditeur : {{ $thread->decryptedSenderName() ?: 'Anonyme' }}
                            @if ($thread->decryptedSenderEmail())
                                ({{ $thread->decryptedSenderEmail() }})
                            @endif
                        </flux:text>
                    @endif
                @endauth
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
                    <flux:heading size="sm">Classification</flux:heading>

                    <form wire:submit="updateClassification" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <flux:select wire:model.live="classificationSection" label="Section" class="flex-1">
                            <flux:select.option value="">Aucune section</flux:select.option>
                            @foreach (\App\Enums\Section::cases() as $sectionOption)
                                <flux:select.option value="{{ $sectionOption->value }}">{{ $sectionOption->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="classificationSchoolClass" label="Classe" class="flex-1">
                            <flux:select.option value="">Aucune classe</flux:select.option>
                            @foreach (\App\Enums\Section::cases() as $sectionOption)
                                <flux:select.group label="{{ $sectionOption->label() }}">
                                    @foreach (\App\Enums\SchoolClass::forSection($sectionOption) as $classeOption)
                                        <flux:select.option value="{{ $classeOption->value }}">{{ $classeOption->label() }}</flux:select.option>
                                    @endforeach
                                </flux:select.group>
                            @endforeach
                        </flux:select>

                        <flux:button type="submit" size="sm">Enregistrer</flux:button>
                    </form>
                </flux:card>

                @if ($this->canComment())
                    <flux:card class="mb-6 space-y-4">
                        <flux:heading size="sm">Commentaire</flux:heading>

                        <form wire:submit="updateComment" class="space-y-3">
                            <flux:textarea
                                wire:model="comment"
                                placeholder="Note interne visible par les parents et professeurs ayant accès à ce dossier…"
                                rows="4"
                            />
                            <flux:button type="submit" size="sm">Enregistrer</flux:button>
                        </form>
                    </flux:card>
                @endif

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
                            multiple
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
                    <div class="max-w-lg rounded-lg px-4 py-3 {{ $msg['is_internal'] ? 'bg-amber-50' : ($msg['author_type'] === 'member' ? 'bg-blue-50' : 'bg-gray-100') }}">
                        <flux:text class="text-xs text-gray-500 mb-1">
                            {{ $msg['author_label'] }}
                            @if ($msg['is_internal'])
                                · <span class="font-medium text-amber-600">Interne</span>
                            @endif
                            · {{ $msg['created_at']->format('d/m/Y H:i') }}
                        </flux:text>
                        <p class="whitespace-pre-line">{{ $msg['plaintext'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="reply" class="space-y-4">
            <flux:card class="space-y-4 {{ $replyVisibility === 'internal' ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900' : '' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon
                            :name="$replyVisibility === 'internal' ? 'lock-closed' : 'chat-bubble-left-right'"
                            variant="micro"
                            class="{{ $replyVisibility === 'internal' ? 'text-amber-600 dark:text-amber-500' : 'text-zinc-400' }}"
                        />
                        <flux:label class="!mb-0">
                            {{ $replyVisibility === 'internal' ? 'Note interne' : 'Votre réponse' }}
                        </flux:label>
                    </div>

                    @if ($this->canReplyInternally())
                        <flux:radio.group wire:model.live="replyVisibility" variant="segmented" size="sm">
                            <flux:radio value="sender" label="Expéditeur" icon="user-circle" />
                            &nbsp;&nbsp;
                            <flux:radio value="internal" label="Interne" icon="lock-closed" />
                        </flux:radio.group>
                    @endif
                </div>

                <flux:textarea
                    wire:model="newMessage"
                    rows="4"
                    :placeholder="$replyVisibility === 'internal' ? 'Ajouter une note visible uniquement par l\'équipe...' : 'Écrivez votre réponse...'"
                />

                @if ($replyVisibility === 'internal')
                    <flux:text size="sm" class="text-amber-700 dark:text-amber-500 flex items-center gap-1">
                        <flux:icon name="information-circle" variant="micro" />
                        Cette note ne sera visible que par l'équipe interne.
                    </flux:text>
                @endif

                <div class="flex justify-end">
                    <flux:button
                        type="submit"
                        :variant="$replyVisibility === 'internal' ? 'filled' : 'primary'"
                        icon="paper-airplane"
                    >
                        {{ $replyVisibility === 'internal' ? 'Ajouter la note' : 'Répondre' }}
                    </flux:button>
                </div>
            </flux:card>
        </form>
    @endif
</div>
