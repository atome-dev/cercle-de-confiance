<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')

        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="min-h-screen bg-surface font-sans text-text antialiased">
        <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 border-b border-border bg-surface/95 backdrop-blur">
            <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
                <div class="flex h-20 items-center justify-between gap-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                        <img src="{{ asset('images/logo.svg') }}" alt="Cercle de Confiance" class="h-10 w-auto">
                        <span class="font-display text-xl text-text">Cercle de Confiance</span>
                    </a>

                    <nav class="hidden items-center gap-8 md:flex">
                        <a
                            href="{{ route('home') }}"
                            class="relative py-2 font-medium transition {{ request()->routeIs('home') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            wire:navigate
                            data-test="nav-home"
                        >
                            Accueil
                        </a>
                        <a
                            href="{{ route('contact.show') }}"
                            class="relative py-2 font-medium transition {{ request()->routeIs('contact.show') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            wire:navigate
                            data-test="nav-contact"
                        >
                            Nous Contacter
                        </a>
                        <a
                            href="{{ route('charte.show') }}"
                            class="relative py-2 font-medium transition {{ request()->routeIs('charte.show') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            wire:navigate
                            data-test="nav-charte"
                        >
                            Notre Charte
                        </a>

                        @hasanyrole('administrateur|parent|professeur')
                        <a
                            href="{{ route('threads.index') }}"
                            class="relative inline-flex items-center gap-1.5 py-2 font-medium transition {{ request()->routeIs('threads.index', 'threads.show') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            wire:navigate
                            data-test="nav-dossiers"
                        >
                            @if (auth()->user()->hasUnreadThreads())
                                <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500" title="{{ __('Dossiers non lus') }}"></span>
                            @endif
                            Dossiers
                        </a>
                        {{--
                        <a
                            href="{{ route('pdf-editor.show') }}"
                            class="relative py-2 font-medium transition {{ request()->routeIs('pdf-editor.show') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            data-test="nav-pdf-editor"
                        >
                            Éditeur PDF
                        </a>
                        --}}
                        {{-- Pas de wire:navigate : la page charge un bundle JS/CSS dédié
                             (resources/js/pdf-editor.js) qui s'exécute une seule fois par
                             chargement de module. En navigation SPA, le script inline qui
                             définit window.PDF_EDITOR_CONFIG est simplement patché par le
                             morph de Livewire (pas ré-exécuté par le navigateur), donc le
                             modèle ne se charge plus automatiquement après la première
                             visite — seul un vrai rechargement de page le garantit. --}}
                        <a
                            href="{{ route('attestation-benevolat.show') }}"
                            class="relative inline-flex items-center gap-1.5 py-2 font-medium transition {{ request()->routeIs('attestation-benevolat.show') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            data-test="nav-attestation-benevolat"
                        >
                            @unless (auth()->user()->volunteerAttestation)
                                <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500" title="{{ __('Attestation non enregistrée') }}"></span>
                            @endunless
                            Attestation de bénévolat
                        </a>
                        @endhasanyrole

                        @guest
                        <a
                            href="{{ route('anonymous-access') }}"
                            class="relative py-2 font-medium transition {{ request()->routeIs('anonymous-access') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            wire:navigate
                            data-test="nav-anonymous-access"
                        >
                            Suivre mon dossier
                        </a>
                        @endguest

                        @role('administrateur')
                        <a href="{{ route('membres.show') }}" class="relative py-2 font-medium text-text transition hover:text-primary-500">
                            Membres
                        </a>

                        <a href="{{ route('cartouches.show') }}" class="relative py-2 font-medium text-text transition hover:text-primary-500">
                            Cartouches
                        </a>

                        <a href="{{ route('attestations-benevolat.index') }}" class="relative py-2 font-medium text-text transition hover:text-primary-500">
                            Attestations
                        </a>

                        @endrole
                    </nav>

                    @auth
                        <flux:dropdown position="bottom" align="end" class="hidden md:block">
                            <button type="button" class="flex items-center gap-2" data-test="user-menu-trigger">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :src="auth()->user()->photo_url"
                                    size="sm"
                                    circle
                                />
                            </button>

                            <flux:menu>
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="auth()->user()->name"
                                        :src="auth()->user()->photo_url"
                                        circle
                                    />
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                                <flux:menu.separator />
                                <flux:menu.item
                                    :href="route('security.edit')"
                                    icon="key"
                                    wire:navigate
                                    data-test="nav-password"
                                >
                                    Mot de passe
                                </flux:menu.item>
                                <flux:menu.separator />
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item
                                        as="button"
                                        type="submit"
                                        icon="arrow-right-start-on-rectangle"
                                        class="w-full cursor-pointer"
                                        data-test="logout-button"
                                    >
                                        Déconnexion
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @endauth

                    <button
                        type="button"
                        class="flex items-center justify-center rounded-md p-2 md:hidden"
                        aria-label="Menu"
                        :aria-expanded="mobileMenuOpen"
                        @click="mobileMenuOpen = ! mobileMenuOpen"
                    >
                        <flux:icon name="bars-2" x-show="! mobileMenuOpen" class="size-6 text-text" />
                        <flux:icon name="x-mark" x-show="mobileMenuOpen" x-cloak class="size-6 text-text" />
                    </button>
                </div>

                <nav
                    x-show="mobileMenuOpen"
                    x-cloak
                    x-collapse
                    @click.outside="mobileMenuOpen = false"
                    class="flex flex-col gap-1 pb-6 md:hidden"
                >
                    <a
                        href="{{ route('home') }}"
                        class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('home') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        wire:navigate
                    >
                        Accueil
                    </a>
                    <a
                        href="{{ route('contact.show') }}"
                        class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('contact.show') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        wire:navigate
                    >
                        Nous Contacter
                    </a>
                    <a
                        href="{{ route('charte.show') }}"
                        class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('charte.show') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        wire:navigate
                    >
                        Notre Charte
                    </a>

                    @hasanyrole('administrateur|parent|professeur')
                    <a
                        href="{{ route('threads.index') }}"
                        class="flex items-center gap-1.5 rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('threads.index', 'threads.show') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        wire:navigate
                    >
                        @if (auth()->user()->hasUnreadThreads())
                            <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500" title="{{ __('Dossiers non lus') }}"></span>
                        @endif
                        Dossiers
                    </a>

                    {{--
                    <a
                        href="{{ route('pdf-editor.show') }}"
                        class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('pdf-editor.show') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        data-test="nav-pdf-editor"
                    >
                        Éditeur PDF
                    </a>
                    --}}
                    <a
                        href="{{ route('attestation-benevolat.show') }}"
                        class="flex items-center gap-1.5 rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('attestation-benevolat.show') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        data-test="nav-attestation-benevolat"
                    >
                        @unless (auth()->user()->volunteerAttestation)
                            <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500" title="{{ __('Attestation non enregistrée') }}"></span>
                        @endunless
                        Attestation de bénévolat
                    </a>
                    @endhasanyrole

                    @guest
                    <a
                        href="{{ route('anonymous-access') }}"
                        class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('anonymous-access') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        wire:navigate
                    >
                        Suivre mon dossier
                    </a>
                    @endguest

                    @auth
                        <div class="mt-2 flex items-center gap-3 border-t border-border px-4 pt-4">
                            <flux:avatar
                                :name="auth()->user()->name"
                                :src="auth()->user()->photo_url"
                                size="sm"
                                circle
                            />
                            <span class="flex-1 truncate font-medium text-text">{{ auth()->user()->name }}</span>
                        </div>
                        <a
                            href="{{ route('security.edit') }}"
                            class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('security.edit') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                            wire:navigate
                        >
                            Mot de passe
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="px-4">
                            @csrf
                            <button type="submit" class="font-medium text-text transition hover:text-primary-500" data-test="mobile-logout-button">
                                Déconnexion
                            </button>
                        </form>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-text text-surface-muted">
            <div class="mx-auto max-w-[1200px] px-6 py-16 lg:px-12">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.svg') }}" alt="" class="h-8 w-auto" aria-hidden="true">
                        <span class="font-display text-lg text-surface">Cercle de Confiance</span>
                    </div>

                    <nav class="flex flex-wrap gap-x-8 gap-y-4 text-sm">
                        <a href="{{ route('contact.show') }}" class="transition hover:text-secondary-400" wire:navigate>Nous Contacter</a>
                        <a href="{{ route('charte.show') }}" class="transition hover:text-secondary-400" wire:navigate>Notre Charte</a>
                        <a href="{{ route('rgpd.show') }}" class="transition hover:text-secondary-400" wire:navigate data-test="nav-rgpd">Protection des données</a>
                        @if(auth()->check())
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="cursor-pointer transition hover:text-secondary-400">Déconnexion</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="transition hover:text-secondary-400" wire:navigate>Connexion membres</a>
                        @endif

                        @if (config('app.debug'))
                            <div class="bottom-4 right-4 z-50">
                                <livewire:clear-cache-button />
                            </div>
                        @endif

                    </nav>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
