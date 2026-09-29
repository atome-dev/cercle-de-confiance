<x-layouts::public :title="__('Notre Charte')">
    {{-- Page title --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-surface-muted to-surface text-center">
        {{-- Decorative background elements --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-primary-500/5 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-secondary-500/5 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 inline-flex items-center justify-center rounded-full bg-primary-500/10 p-4">
                    <flux:icon name="shield-check" class="size-8 text-primary-500" />
                </div>
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Notre Charte') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Découvrez la raison d’être, le rôle et les limites du Cercle de Confiance.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Charter content --}}
    <section class="py-10">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-[800px]">

                {{-- Raison d'être --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="heart" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Raison d’être') }}
                        </h3>
                    </div>
                    <p class="leading-[1.8] text-text-muted">
                        Le Cercle de Confiance est une instance de médiation et de recours, qui veille à ce que chacune et chacun puisse trouver, au sein de l’école, un espace d’écoute et de dialogue lorsqu’une difficulté ne peut être résolue directement. Il contribue à préserver ou restaurer la confiance entre les personnes et à rechercher, ensemble, une voie permettant de faire évoluer la situation.
                    </p>
                </div>

                {{-- Résultats attendus du cercle --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="flag" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Résultats attendus du cercle') }}
                        </h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance cherche à permettre à chaque personne qui le sollicite d’être entendue et de recevoir une réponse. Selon les situations, il vise à contribuer à la résolution ou à l’apaisement de la difficulté, au rétablissement du dialogue et de la confiance entre les personnes, ou à l’orientation vers l’instance la plus à même d’agir.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Il contribue également, à partir de situations rencontrées, à faire évoluer les pratiques et le fonctionnement de l’école lorsque cela apparaît nécessaire.
                    </p>
                </div>

                {{-- Rôle et périmètre --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="user-group" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Rôle et périmètre') }}
                        </h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance s’adresse à tous les adultes de l’école et à toutes les instances (CA, CDP, GC, collèges de cycle, admin, cantine, salarié.e.s).
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Il peut être sollicité pour toute difficulté d’ordre relationnel, personnel, ou touchant à la communication entre des personnes ou avec l’école. Il n’a pas vocation à traiter des questions purement matérielles, administratives, financières ou pédagogiques, qui relèvent des instances compétentes : il peut en revanche être sollicité lorsqu’une difficulté relationnelle se noue autour de l’une de ces questions.
                    </p>
                </div>

                {{-- Missions principales --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="hand-raised" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Missions principales') }}
                        </h3>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-xl bg-surface-muted p-6">
                            <strong class="text-text">{{ __('Accompagner une situation individuelle') }}</strong>
                            <ul class="mt-4 space-y-3">
                                @foreach ([
                                    'Écouter une personne confrontée à une difficulté et accueillir sa parole sans jugement,',
                                    'Aider à clarifier une situation et les différents points de vue,',
                                    'Mettre en relation les personnes et faciliter la communication lorsqu’un dialogue direct est difficile,',
                                    'Orienter vers l’instance appropriée lorsque la situation ne relève pas de ses compétences,',
                                    'Accompagner une démarche de médiation lorsque celle-ci paraît adaptée,',
                                    'Proposer des pistes ou solutions, sans se substituer à la décision des personnes ou des instances compétentes,',
                                    'Accompagner la personne dans cette orientation, lorsque cela est nécessaire ou souhaité,',
                                    'Assurer un suivi de la situation jusqu’à sa résolution, son apaisement ou son orientation effective.',
                                ] as $mission)
                                    <li class="flex gap-3">
                                        <flux:icon name="check-circle" class="mt-1 size-5 shrink-0 text-secondary-500" />
                                        <span class="leading-relaxed text-text-muted">{{ $mission }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-xl bg-surface-muted p-6">
                            <strong class="text-text">{{ __('Agir au niveau collectif') }}</strong>
                            <ul class="mt-4 space-y-3">
                                @foreach ([
                                    'Soutenir les parents référents dans leur rôle : disponibilité et conseil en cas de besoin, animer le groupe de parents référents, recueillir leurs retours et faire remonter les difficultés collectives des classes.',
                                    'Faire remonter des difficultés récurrentes ou des dysfonctionnements aux instances compétentes,',
                                    'Proposer des pistes d’amélioration du fonctionnement collectif, lorsqu’elles émergent des situations rencontrées,',
                                    'Contribuer à prévenir l’apparition ou l’aggravation de certaines difficultés en favorisant le dialogue.',
                                ] as $mission)
                                    <li class="flex gap-3">
                                        <flux:icon name="check-circle" class="mt-1 size-5 shrink-0 text-secondary-500" />
                                        <span class="leading-relaxed text-text-muted">{{ $mission }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Missions complémentaires --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Missions complémentaires') }}
                        </h3>
                    </div>
                    <p class="mb-6 leading-[1.8] text-text-muted">
                        Le Cercle peut également être sollicité pour faciliter ponctuellement un échange collectif ou une réunion de classe, ou pour contribuer à une réflexion sur un dysfonctionnement collectif. Ces interventions restent secondaires par rapport à sa mission principale et sont mises en œuvre lorsque le Cercle dispose des capacités suffisantes.
                    </p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Faciliter une discussion ou un dialogue collectif lorsque cela peut contribuer à résoudre une difficulté ;',
                            'Intervenir ponctuellement dans une réunion de classe lorsqu’une difficulté de communication ou un conflit le justifie.',
                        ] as $mission)
                            <li class="flex gap-3">
                                <flux:icon name="check-circle" class="mt-1 size-5 shrink-0 text-secondary-500" />
                                <span class="leading-relaxed text-text-muted">{{ $mission }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Les limites du Cercle de confiance --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="scale" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Les limites du Cercle de Confiance') }}
                        </h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle ne se substitue pas :
                    </p>
                    <ul class="mb-6 space-y-3">
                        @foreach ([
                            'aux personnes concernées, qui restent autant que possible actrices de la résolution de leur difficulté,',
                            'aux enseignant.e.s / jardinier.ère.s / interlocuteur.rice.s habituel.le.s, qui doivent être sollicité.e.s en premier lorsque cela est possible,',
                            'aux instances de l’école (CA, CDP, GC et collèges de cycle) pour les questions relevant de leur responsabilité,',
                            'à la cellule Care, notamment pour les situations relevant du harcèlement ou de problématiques spécifiques concernant les enfants,',
                            'aux autorités compétentes, notamment la police ou les services d’urgence, lorsqu’une situation présente un danger ou relève d’une obligation légale.',
                        ] as $limit)
                            <li class="flex gap-3">
                                <flux:icon name="minus-circle" class="mt-1 size-5 shrink-0 text-secondary-500" />
                                <span class="leading-relaxed text-text-muted">{{ $limit }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance ne signale ni ne transmet une situation à un tiers (personne ou instance interne à l’école, autorité extérieure) qu’avec l’accord de la personne concernée, sauf lorsque la loi l’exige.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Le Cercle de Confiance peut accompagner une personne dans les démarches nécessaires auprès de l’instance ou de l’autorité compétente, notamment lorsque la situation est difficile à porter seul. Il ne se substitue toutefois pas à cette instance ou à cette autorité.
                    </p>
                </div>

                {{-- CTA --}}
                <div class="mt-16 rounded-2xl border border-border bg-gradient-to-br from-surface-muted to-surface p-10 text-center">
                    <h4 class="mb-3 font-display text-xl text-text">
                        {{ __('Une question, une préoccupation ?') }}
                    </h4>
                    <p class="mb-6 text-text-muted">
                        {{ __('Le Cercle de Confiance est à votre écoute, en toute confidentialité.') }}
                    </p>
                    <flux:button
                        href="#"
                        variant="primary"
                        icon:trailing="arrow-right"
                        class="[--color-accent:var(--color-primary-500)] [--color-accent-content:var(--color-primary-500)] [--color-accent-foreground:var(--color-surface)]"
                    >
                        {{ __('Nous contacter') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
