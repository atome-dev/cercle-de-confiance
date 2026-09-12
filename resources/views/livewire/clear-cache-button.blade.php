<div>
    @if (config('app.debug'))
        <div class="flex items-center gap-3">
            <flux:button
                wire:click="clearCache"
                wire:loading.attr="disabled"
                wire:target="clearCache"
                variant="danger"
                size="sm"
                icon="trash"
            >
                <span wire:loading.remove wire:target="clearCache">
                    Vider le cache
                </span>
                <span wire:loading wire:target="clearCache">
                    Vidage en cours...
                </span>
            </flux:button>

            @if ($message)
                <flux:badge color="{{ str_contains($message, 'succès') ? 'green' : 'red' }}">
                    {{ $message }}
                </flux:badge>
            @endif
        </div>
    @endif
</div>
