<div>
    {{-- En-tête compacte : contrairement aux autres pages membres, cet outil occupe
         l'essentiel du viewport, donc pas de grand hero comme sur /cartouches. --}}
    <section class="border-b border-border bg-surface-muted px-6 py-6 lg:px-12">
        <div class="mx-auto max-w-[1200px]">
            <h1 class="font-display text-2xl text-text sm:text-3xl">{{ __('Éditeur PDF') }}</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ __('Annotez, surlignez et modifiez vos PDF directement dans le navigateur — rien n’est envoyé à un serveur.') }}
            </p>
        </div>
    </section>

    <script>window.PDF_EDITOR_CONFIG = {};</script>

    @include('livewire.partials.pdf-editor-app')
</div>

@push('head')
    @vite(['resources/css/pdf-editor.css', 'resources/js/pdf-editor.js'])
@endpush
