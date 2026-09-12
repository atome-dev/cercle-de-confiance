<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Accès protégé')" :description="__('Saisissez le code d\'accès communiqué dans l\'établissement.')" />

    <form wire:submit="attempt" class="flex flex-col gap-6">
        <div>
            <flux:input
                wire:model="code"
                :label="__('Code d\'accès')"
                type="text"
                required
                autofocus
                autocomplete="off"
            />
            <flux:error name="code" />
        </div>

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Valider') }}
        </flux:button>
    </form>
</div>
