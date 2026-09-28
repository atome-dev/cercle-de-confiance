<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Suppression de compte') }}</flux:heading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" data-test="delete-user-button">
            {{ __('Supprimer mon compte') }}
        </flux:button>
    </flux:modal.trigger>

    <livewire:settings.delete-user-modal />
</section>
