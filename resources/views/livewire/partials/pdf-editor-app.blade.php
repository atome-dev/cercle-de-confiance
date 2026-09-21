{{--
    Toolbar + canvas de l'éditeur PDF, partagés par la page générique
    (livewire.pdf-editor) et la page "Attestation de bénévolat"
    (livewire.volunteer-attestation). Le composant hôte doit :
    - définir `window.PDF_EDITOR_CONFIG` avant d'inclure ce partial (voir
      resources/js/pdf-editor.js pour les clés supportées) ;
    - charger @vite(['resources/css/pdf-editor.css', 'resources/js/pdf-editor.js'])
      via @push('head').
--}}
<div id="app" style="height: calc(100vh - 5rem)">

    {{-- ============ TOOLBAR ============ --}}
    <header class="toolbar">

        <div class="toolbar-group">
            <flux:button id="btn-open" icon="folder-open" title="{{ __('Ouvrir un PDF') }}">
                {{ __('Ouvrir') }}
            </flux:button>
            <flux:button id="btn-save" variant="primary" icon="arrow-down-tray" title="{{ __('Enregistrer le PDF édité') }}" disabled>
                {{ __('Enregistrer') }}
            </flux:button>
        </div>

        <div class="toolbar-sep"></div>

        <div class="toolbar-group" id="tool-group">
            <button class="btn tool-btn active" data-tool="select" title="{{ __('Sélectionner / Déplacer') }}">
                <svg viewBox="0 0 24 24"><path d="M5 3l14 7-6 2-2 6-6-15z" fill="currentColor"/></svg>
            </button>
            <button class="btn tool-btn" data-tool="text" title="{{ __('Ajouter du texte') }}">
                <svg viewBox="0 0 24 24"><path d="M5 5h14M12 5v14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <button class="btn tool-btn" data-tool="draw" title="{{ __('Dessiner à main levée') }}">
                <svg viewBox="0 0 24 24"><path d="M4 20l1-4L16 5l3 3L8 19l-4 1z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </button>
            <button class="btn tool-btn" data-tool="highlight" title="{{ __('Surligner') }}">
                <svg viewBox="0 0 24 24"><path d="M6 15l7-7 3 3-7 7H6v-3z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M4 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <button class="btn tool-btn" data-tool="rect" title="{{ __('Rectangle') }}">
                <svg viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
            </button>
            <button class="btn tool-btn" data-tool="image" title="{{ __('Insérer une image') }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="9" cy="10" r="1.8" fill="currentColor"/><path d="M4 17l5-5 4 4 3-3 4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="toolbar-sep"></div>

        <div class="toolbar-group" id="tool-options">
            <label class="opt" data-for="color">
                <span>{{ __('Couleur') }}</span>
                <input type="color" id="opt-color" value="#e53935">
            </label>
            <label class="opt" data-for="fontSize">
                <span>{{ __('Taille') }}</span>
                <input type="number" id="opt-fontsize" value="16" min="6" max="96" step="1">
            </label>
            <label class="opt" data-for="strokeWidth">
                <span>{{ __('Épaisseur') }}</span>
                <input type="range" id="opt-strokewidth" value="3" min="1" max="20" step="1">
            </label>
        </div>

        <div class="toolbar-sep"></div>

        <div class="toolbar-group">
            <button id="btn-undo" class="btn icon-only" title="{{ __('Annuler (Ctrl+Z)') }}" disabled>
                <svg viewBox="0 0 24 24"><path d="M9 7L4 12l5 5M4 12h11a5 5 0 0 1 0 10h-1" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button id="btn-redo" class="btn icon-only" title="{{ __('Rétablir (Ctrl+Y)') }}" disabled>
                <svg viewBox="0 0 24 24"><path d="M15 7l5 5-5 5M20 12H9a5 5 0 0 0 0 10h1" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="toolbar-sep"></div>

        <div class="toolbar-group">
            <button id="btn-zoom-out" class="btn icon-only" title="{{ __('Zoom arrière') }}">−</button>
            <span id="zoom-label">100%</span>
            <button id="btn-zoom-in" class="btn icon-only" title="{{ __('Zoom avant') }}">+</button>
        </div>

        <div class="toolbar-group toolbar-group-right">
            <button id="btn-prev-page" class="btn icon-only" title="{{ __('Page précédente') }}">‹</button>
            <span id="page-label">0 / 0</span>
            <button id="btn-next-page" class="btn icon-only" title="{{ __('Page suivante') }}">›</button>
        </div>
    </header>

    {{-- ============ WORKSPACE ============ --}}
    <main class="workspace">
        <aside class="thumbnails" id="thumbnails">
            <div class="thumbnails-list" id="thumbnails-list"></div>
            <flux:button id="btn-add-page" class="w-full" icon="plus" title="{{ __('Ajouter une page blanche') }}" disabled>
                {{ __('Page') }}
            </flux:button>
        </aside>

        <section class="viewer" id="viewer">
            <div class="empty-state" id="empty-state">
                <svg viewBox="0 0 24 24" width="56" height="56"><path d="M4 4h9l5 5v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="1.2"/><path d="M13 4v5h5" fill="none" stroke="currentColor" stroke-width="1.2"/></svg>
                <p>{{ __('Glissez-déposez un fichier PDF ici') }}<br>{{ __('ou') }}</p>
                <flux:button id="btn-open-empty" variant="primary" icon="folder-open">
                    {{ __('Choisir un fichier') }}
                </flux:button>
            </div>
            <div class="page-scroll" id="page-scroll">
                <div class="page-wrap" id="page-wrap" hidden>
                    <canvas id="pdf-canvas"></canvas>
                    <div class="annotation-layer" id="annotation-layer"></div>
                </div>
            </div>
        </section>
    </main>

    <input type="file" id="file-input" accept="application/pdf" hidden>
    <input type="file" id="image-input" accept="image/png,image/jpeg" hidden>

    <div id="toast" class="toast" role="alert" hidden></div>
</div>

