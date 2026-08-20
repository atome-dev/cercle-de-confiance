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
                            href="{{ route('charte.show') }}"
                            class="relative py-2 font-medium transition {{ request()->routeIs('charte.show') ? 'text-primary-500' : 'text-text hover:text-primary-500' }}"
                            wire:navigate
                            data-test="nav-charte"
                        >
                            Notre Charte
                        </a>
                        <a href="#" class="relative py-2 font-medium text-text transition hover:text-primary-500">
                            Nous Contacter
                        </a>
                    </nav>

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
                        href="{{ route('charte.show') }}"
                        class="rounded-md px-4 py-3 font-medium transition {{ request()->routeIs('charte.show') ? 'bg-surface-muted text-primary-500' : 'text-text hover:bg-surface-muted hover:text-primary-500' }}"
                        wire:navigate
                    >
                        Notre Charte
                    </a>
                    <a href="#" class="rounded-md px-4 py-3 font-medium text-text transition hover:bg-surface-muted hover:text-primary-500">
                        Nous Contacter
                    </a>
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
                        <a href="{{ route('charte.show') }}" class="transition hover:text-secondary-400" wire:navigate>Notre Charte</a>
                        <a href="#" class="transition hover:text-secondary-400">Nous Contacter</a>
                        <a href="{{ route('login') }}" class="transition hover:text-secondary-400" wire:navigate>Connexion membres</a>
                    </nav>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
