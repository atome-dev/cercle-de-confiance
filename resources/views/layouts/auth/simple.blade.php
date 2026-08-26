<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface font-sans text-text antialiased">
        <div class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden p-6 md:p-10">
            <div class="pointer-events-none absolute -top-32 -left-32 h-80 w-80 rounded-full bg-secondary-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-32 -bottom-32 h-80 w-80 rounded-full bg-primary-200/40 blur-3xl"></div>

            <div class="relative flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2" wire:navigate>
                    <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-14 w-auto">
                    <span class="font-display text-lg text-text">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="flex flex-col gap-6 rounded-2xl border border-border bg-surface p-8 shadow-lg shadow-primary-950/5">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
