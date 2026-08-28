<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Dossiers</flux:heading>

        <flux:select wire:model.live="statusFilter" class="w-48">
            <flux:select.option value="all">Tous les statuts</flux:select.option>
            <flux:select.option value="nouveau">Nouveau</flux:select.option>
            <flux:select.option value="en_cours">En cours</flux:select.option>
            <flux:select.option value="archive">Archivé</flux:select.option>
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Code</flux:table.column>
            <flux:table.column>Destinataire</flux:table.column>
            <flux:table.column>Statut</flux:table.column>
            <flux:table.column>Dernier message</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->threads as $thread)
                <flux:table.row wire:key="thread-{{ $thread->id }}">
                    <flux:table.cell>
                        <flux:link href="{{ route('threads.show', $thread) }}" wire:navigate>
                            {{ $thread->code }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $thread->isForGroup() ? 'Commission' : $thread->recipientUser?->name }}
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
                    <flux:table.cell colspan="4" class="text-center text-gray-500">
                        Aucun dossier pour le moment.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$this->threads" class="mt-4" />
</div>
