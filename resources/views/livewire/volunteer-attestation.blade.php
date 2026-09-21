<div>
    @if ($submitted)
        <div class="mx-auto max-w-2xl px-6 py-16 sm:px-12">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-success-muted">
                    <flux:icon name="check-circle" class="h-8 w-8 text-success" />
                </div>
                <flux:heading size="xl">{{ __('Attestation enregistrée') }}</flux:heading>
                <flux:text class="mt-2 text-text-muted">
                    {{ __('Vous avez soumis votre attestation de bénévolat le :date.', ['date' => $submitted->submitted_at->format('d/m/Y à H:i')]) }}
                </flux:text>
            </div>

            <div class="flex justify-center">
                <flux:button href="{{ route('attestation-benevolat.download') }}" variant="primary" icon="arrow-down-tray">
                    {{ __('Télécharger ma copie') }}
                </flux:button>
            </div>
        </div>
    @else
        {{-- En-tête compacte, comme /editeur-pdf : l'outil occupe l'essentiel du viewport. --}}
        <section class="border-b border-border bg-surface-muted px-6 py-6 lg:px-12">
            <div class="mx-auto max-w-[1200px]">
                <h1 class="font-display text-2xl text-text sm:text-3xl">{{ __('Attestation de bénévolat') }}</h1>
                <p class="mt-1 text-sm text-text-muted">
                    {{ __('Remplissez votre attestation ci-dessous, puis cliquez sur Enregistrer. Cette action est définitive : votre copie sera transmise à l’administration et ne pourra plus être modifiée.') }}
                </p>
            </div>
        </section>

        <script>
            window.PDF_EDITOR_CONFIG = {
                initialFileUrl: @json(route('attestation-benevolat.template')),
                saveMode: 'upload',
                saveUrl: @json(route('attestation-benevolat.store')),
                afterSaveUrl: @json(route('attestation-benevolat.show')),
            };
        </script>

        @include('livewire.partials.pdf-editor-app')
    @endif
</div>

@push('head')
    @vite(['resources/css/pdf-editor.css', 'resources/js/pdf-editor.js'])
@endpush
