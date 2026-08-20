<x-layouts::public :title="__('Notre Charte')">
    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
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
                <div class="mb-16">
                    <h3 class="relative mb-6 pl-8 font-display text-2xl text-primary-500 before:absolute before:top-1/2 before:left-0 before:h-[60%] before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-gradient-to-b before:from-secondary-500 before:to-primary-500">
                        {{ __('Nos valeurs fondamentales') }}
                    </h3>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance repose sur trois piliers essentiels qui constituent le socle de notre action : l'écoute attentive, la bienveillance sincère et le respect mutuel. Ces valeurs ne sont pas de simples mots affichés sur un mur ; elles guident chacune de nos interactions et chacune des décisions que nous prenons au sein de la communauté scolaire.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Nous croyons fermement que chaque membre de la communauté scolaire mérite d'être entendu et respecté, quelle que soit sa situation. Notre rôle est de créer un espace sûr où chacun peut exprimer ses préoccupations en toute confiance.
                    </p>
                </div>

                <div class="mb-16">
                    <h3 class="relative mb-6 pl-8 font-display text-2xl text-primary-500 before:absolute before:top-1/2 before:left-0 before:h-[60%] before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-gradient-to-b before:from-secondary-500 before:to-primary-500">
                        {{ __('Confidentialité') }}
                    </h3>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        La confidentialité est le fondement de notre crédibilité et de notre capacité à aider. Toutes les informations partagées avec le Cercle de Confiance sont traitées avec le plus grand respect et ne sont jamais communiquées à des tiers, sauf demande explicite de la personne concernée.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Les échanges avec les membres du Cercle restent strictement confidentiels. Aucune information n'est divulguée sans votre consentement explicite, et ce, quelle que soit la nature de votre demande.
                    </p>
                </div>

                <div class="mb-16">
                    <h3 class="relative mb-6 pl-8 font-display text-2xl text-primary-500 before:absolute before:top-1/2 before:left-0 before:h-[60%] before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-gradient-to-b before:from-secondary-500 before:to-primary-500">
                        {{ __('Neutralité') }}
                    </h3>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance agit en toute indépendance vis-à-vis de la direction de l'établissement, des enseignants et de tout autre organisme extérieur. Notre unique objectif est de vous aider à trouver la meilleure solution possible, sans considération d'intérêt personnel ou organisationnel.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Nous nous engageons à analyser chaque situation avec objectivité, à considérer toutes les parties prenantes avec équité, et à proposer des solutions justes et équilibrées, sans prendre parti.
                    </p>
                </div>

                <div class="mb-16">
                    <h3 class="relative mb-6 pl-8 font-display text-2xl text-primary-500 before:absolute before:top-1/2 before:left-0 before:h-[60%] before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-gradient-to-b before:from-secondary-500 before:to-primary-500">
                        {{ __('Nos engagements envers vous') }}
                    </h3>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        En faisant appel au Cercle de Confiance, vous bénéficiez de notre engagement total à vous accompagner dans le respect de vos droits et de votre dignité :
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        <strong class="text-text">{{ __('Accessibilité :') }}</strong> {{ __('Nous sommes à votre disposition pour répondre à vos demandes dans les meilleurs délais. Vous pouvez nous contacter anonymement si vous le souhaitez.') }}<br><br>
                        <strong class="text-text">{{ __('Écoute active :') }}</strong> {{ __('Chaque demande reçoit toute notre attention. Nous prenons le temps nécessaire pour bien comprendre votre situation.') }}<br><br>
                        <strong class="text-text">{{ __('Suivi rigoureux :') }}</strong> {{ __('Votre dossier est traité avec sérieux et nous vous tenons informé de l\'avancement de votre demande.') }}<br><br>
                        <strong class="text-text">{{ __('Accompagnement adapté :') }}</strong> {{ __('Nous vous guidons vers les ressources et les personnes compétentes pour résoudre vos difficultés.') }}
                    </p>
                </div>

                <div class="mb-16">
                    <h3 class="relative mb-6 pl-8 font-display text-2xl text-primary-500 before:absolute before:top-1/2 before:left-0 before:h-[60%] before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-gradient-to-b before:from-secondary-500 before:to-primary-500">
                        {{ __('Composition du Cercle') }}
                    </h3>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le Cercle de Confiance est composé de huit membres représentatifs de la diversité de notre communauté scolaire : cinq parents d'élèves, deux professeurs et un salarié. Cette composition garantit une pluralité de regards et une compréhension approfondie des différentes réalités vécues au sein de l'établissement.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Chaque membre s'engage à respecter la présente charte et à maintenir les plus hauts standards d'éthique et de professionnalisme dans l'exercice de ses fonctions au sein du Cercle.
                    </p>
                </div>

                {{-- Quote --}}
                <blockquote class="my-12 rounded-r-lg border-l-4 border-secondary-500 bg-gradient-to-br from-surface-muted to-surface p-8 italic">
                    <p class="text-lg text-text">
                        « Le Cercle de Confiance est un espace de parole et de médiation où chacun peut trouver une écoute bienveillante et neutre, sans jugement. Notre unique objectif est de vous accompagner vers des solutions constructives et durables. »
                    </p>
                </blockquote>

                {{-- CTA --}}
                <div class="mt-16 border-t border-border pt-12 text-center">
                    <flux:button
                        href="#"
                        variant="primary"
                        class="[--color-accent:var(--color-primary-500)] [--color-accent-content:var(--color-primary-500)] [--color-accent-foreground:var(--color-surface)]"
                    >
                        {{ __('Nous contacter') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
