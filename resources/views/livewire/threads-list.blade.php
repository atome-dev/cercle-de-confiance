<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Dossiers</flux:heading>
        <flux:switch
            wire:model.live="showArchived"
            label="Afficher les dossiers archivés"
            align="left"
            class="data-[checked]:bg-amber-500"
        />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Code</flux:table.column>
            <flux:table.column>Expéditeur</flux:table.column>
            <flux:table.column>Destinataire</flux:table.column>
            <flux:table.column>Section</flux:table.column>
            <flux:table.column>Classe</flux:table.column>
            <flux:table.column>Statut</flux:table.column>
            <flux:table.column>Dernier message</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->threads as $thread)
                @php $hasUnread = $thread->hasUnreadFor(auth()->user()); @endphp
                <flux:table.row wire:key="thread-{{ $thread->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            @if ($hasUnread)
                                <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500" title="Messages non lus"></span>
                            @endif
                            <flux:link href="{{ route('threads.show', $thread) }}" wire:navigate class="{{ $hasUnread ? 'font-bold' : '' }}">
                                {{ $thread->code }}
                            </flux:link>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $thread->decryptedSenderName() ?: 'Anonyme' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $thread->isForGroup() ? 'Commission' : $thread->recipientUser?->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $thread->section?->label() ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $thread->school_class?->label() ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($thread->status) {
                            'nouveau' => 'blue',
                            'en_cours' => 'amber',
                            'archive' => 'gray',
                        }">
                            {{ str($thread->status)->replace('_', ' ')->title() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $thread->last_message_at?->format('d/m/Y H:i') }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center text-gray-500">
                        Aucun dossier pour le moment.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$this->threads" class="mt-4" />
</div>
