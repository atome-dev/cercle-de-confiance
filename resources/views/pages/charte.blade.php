<x-layouts::public :title="__('Notre Charte')">
    {{-- Page title --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-surface-muted to-surface py-20 text-center">
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
                    {{ __('Découvrez les valeurs et les engagements qui guident notre action au quotidien.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Charter content --}}
    <section class="py-24">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-[800px]">

                {{-- Nos valeurs fondamentales --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="heart" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Nos valeurs fondamentales') }}
                        </h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance repose sur trois piliers essentiels qui constituent le socle de notre action : l'écoute attentive, la bienveillance sincère et le respect mutuel. Ces valeurs ne sont pas de simples mots affichés sur un mur ; elles guident chacune de nos interactions et chacune des décisions que nous prenons au sein de la communauté scolaire.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Nous croyons fermement que chaque membre de la communauté scolaire mérite d'être entendu et respecté, quelle que soit sa situation. Notre rôle est de créer un espace sûr où chacun peut exprimer ses préoccupations en toute confiance.
                    </p>
                </div>

                {{-- Confidentialité --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="lock-closed" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Confidentialité') }}
                        </h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        La confidentialité est le fondement de notre crédibilité et de notre capacité à aider. Toutes les informations partagées avec le Cercle de Confiance sont traitées avec le plus grand respect et ne sont jamais communiquées à des tiers, sauf demande explicite de la personne concernée.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Les échanges avec les membres du Cercle restent strictement confidentiels. Aucune information n'est divulguée sans votre consentement explicite, et ce, quelle que soit la nature de votre demande.
                    </p>
                </div>

                {{-- Neutralité --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="scale" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Neutralité') }}
                        </h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance agit en toute indépendance vis-à-vis de la direction de l'établissement, des enseignants et de tout autre organisme extérieur. Notre unique objectif est de vous aider à trouver la meilleure solution possible, sans considération d'intérêt personnel ou organisationnel.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Nous nous engageons à analyser chaque situation avec objectivité, à considérer toutes les parties prenantes avec équité, et à proposer des solutions justes et équilibrées, sans prendre parti.
                    </p>
                </div>

                {{-- Nos engagements envers vous --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="hand-raised" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Nos engagements envers vous') }}
                        </h3>
                    </div>
                    <p class="mb-6 leading-[1.8] text-text-muted">
                        En faisant appel au Cercle de Confiance, vous bénéficiez de notre engagement total à vous accompagner dans le respect de vos droits et de votre dignité :
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Accessibilité') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Nous sommes à votre disposition pour répondre à vos demandes dans les meilleurs délais. Vous pouvez nous contacter anonymement si vous le souhaitez.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Écoute active') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Chaque demande reçoit toute notre attention. Nous prenons le temps nécessaire pour bien comprendre votre situation.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Suivi rigoureux') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Votre dossier est traité avec sérieux et nous vous tenons informé de l\'avancement de votre demande.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Accompagnement adapté') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Nous vous guidons vers les ressources et les personnes compétentes pour résoudre vos difficultés.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Composition du Cercle --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="user-group" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">
                            {{ __('Composition du Cercle') }}
                        </h3>
                    </div>
                    <p class="mb-6 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance est composé de six membres représentatifs de la diversité de notre communauté scolaire, garantissant une pluralité de regards et une compréhension approfondie des différentes réalités vécues au sein de l'établissement.
                    </p>

                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="rounded-xl bg-surface-muted p-4">
                            <p class="font-display text-3xl text-primary-500">3</p>
                            <p class="mt-1 text-sm text-text-muted">{{ __('Parents d\'élèves') }}</p>
                        </div>
                        <div class="rounded-xl bg-surface-muted p-4">
                            <p class="font-display text-3xl text-primary-500">3</p>
                            <p class="mt-1 text-sm text-text-muted">{{ __('Professeurs') }}</p>
                        </div>
                    </div>

                    <p class="mt-6 leading-[1.8] text-text-muted">
                        Chaque membre s'engage à respecter la présente charte et à maintenir les plus hauts standards d'éthique et de professionnalisme dans l'exercice de ses fonctions au sein du Cercle.
                    </p>
                </div>

                {{-- Quote --}}
                <blockquote class="relative my-12 overflow-hidden rounded-2xl border-l-4 border-secondary-500 bg-gradient-to-br from-surface-muted to-surface p-8 shadow-sm">
                    <flux:icon name="chat-bubble-left-right" class="absolute -top-4 -right-4 size-24 text-primary-500/5" />
                    <p class="relative text-lg leading-relaxed text-text italic">
                        « Le Cercle de Confiance est un espace de parole et de médiation où chacun peut trouver une écoute bienveillante et neutre, sans jugement. Notre unique objectif est de vous accompagner vers des solutions constructives et durables. »
                    </p>
                </blockquote>

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
