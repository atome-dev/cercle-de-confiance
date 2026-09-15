<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-surface-muted to-surface py-10 text-center">
        <div class="relative mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl lg:text-6xl">
                    Un espace d'<span class="text-primary-500">écoute</span>, de <span class="text-primary-500">confiance</span> et de <span class="text-primary-500">bienveillance</span>
                </h1>

                <p class="mx-auto mb-12 max-w-xl text-xl text-text-muted">
                    Le Cercle de Confiance vous accompagne dans la résolution de difficultés ou conflits au sein de l'école.
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    <a
                        href="{{ route('contact.show') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-md border-2 border-primary-500 px-7 py-3.5 font-semibold text-primary-500 transition hover:bg-primary-500 hover:text-white"
                    >
                        Nous contacter
                    </a>
                    <a
                        href="{{ route('charte.show') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-md border-2 border-primary-500 px-7 py-3.5 font-semibold text-primary-500 transition hover:bg-primary-500 hover:text-white"
                    >
                        Notre charte
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Qui sommes-nous --}}
    <section class="py-0">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mb-8 text-center">
                <div class="mb-6 flex items-center justify-center gap-4">
                    <span class="h-0.5 w-15 bg-gradient-to-r from-secondary-500 to-primary-500"></span>
                    <span class="text-2xl text-secondary-500">✦</span>
                    <span class="h-0.5 w-15 bg-gradient-to-l from-secondary-500 to-primary-500"></span>
                </div>
                <h2 class="font-display text-3xl text-text sm:text-4xl">Qui sommes-nous ?</h2>
            </div>

            <div class="grid grid-cols-2 gap-8 md:grid-cols-3">
                @foreach ($members as $member)
                    <flux:card class="!rounded-lg !border-border !bg-surface p-8 text-center shadow-[var(--shadow-color-sm)] transition hover:-translate-y-1 hover:!shadow-[var(--shadow-color-lg)]">
                        <flux:avatar
                            :name="$member['name']"
                            :src="$member['photo_url']"
                            size="xl"
                            circle
                            class="mx-auto mb-4 {{ $this->avatarClasses($member['membre_role']) }}"
                        />

                        <h4 class="mb-2 font-semibold text-text">{{ $member['name'] }}</h4>

                        <span class="mb-3 inline-block rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase {{ $this->badgeClasses($member['membre_role']) }}">
                            {{ $member['membre_titre'] }}
                        </span>

                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Que faisons-nous --}}
    <section class="bg-surface-muted py-10">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mb-8 text-center">
                <div class="mb-6 flex items-center justify-center gap-4">
                    <span class="h-0.5 w-15 bg-gradient-to-r from-secondary-500 to-primary-500"></span>
                    <span class="text-2xl text-secondary-500">✦</span>
                    <span class="h-0.5 w-15 bg-gradient-to-l from-secondary-500 to-primary-500"></span>
                </div>
                <h2 class="font-display text-3xl text-text sm:text-4xl">Que faisons-nous ?</h2>
            </div>

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                @foreach ($features as $feature)
                    <flux:card class="!rounded-lg !border-border !bg-surface p-12 text-center shadow-[var(--shadow-color-sm)] hover:!shadow-[var(--shadow-color-lg)]">
                        <span class="mb-6 block text-5xl">{{ $feature['icone'] }}</span>
                        <h3 class="mb-4 font-display text-2xl text-primary-500">{{ $feature['titre'] }}</h3>
                        <p class="text-text-muted">{{ $feature['description'] }}</p>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Notre engagement --}}
    <section class="bg-gradient-to-br from-primary-500 to-primary-600 py-24 text-white">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl text-center">
                <blockquote class="relative px-8 font-display text-xl italic leading-relaxed sm:text-2xl">
                    Toute démarche auprès du Cercle de Confiance est strictement confidentielle et ne peut en aucun cas entraîner de préjudice pour la personne qui nous sollicite.
                </blockquote>
            </div>
        </div>
    </section>
</div>
