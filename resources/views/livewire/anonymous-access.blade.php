<div class="max-w-md mx-auto p-6">
    <flux:heading size="lg" class="mb-4">Accéder à mon dossier</flux:heading>

    <form wire:submit="submit" class="space-y-4">
        <flux:input
            wire:model="fullCode"
            label="Code de dossier"
            placeholder="ABCD-EFGH"
            class="font-mono uppercase"
        />

        @if ($error)
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$error" />
        @endif

        <flux:button type="submit" variant="primary">Accéder au dossier</flux:button>
    </form>
</div>
