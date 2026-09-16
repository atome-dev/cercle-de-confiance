<x-layouts::public :title="__('Protection des données personnelles')">
    {{-- Page title --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-surface-muted to-surface py-20 text-center">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-primary-500/5 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-secondary-500/5 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 inline-flex items-center justify-center rounded-full bg-primary-500/10 p-4">
                    <flux:icon name="lock-closed" class="size-8 text-primary-500" />
                </div>
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Protection des données personnelles') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Comment le Cercle de Confiance collecte, protège et vous permet de contrôler vos données, conformément au RGPD.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="py-6">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-[800px]">

                {{-- Responsable du traitement --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="building-office" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Responsable du traitement') }}</h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le responsable du traitement des données collectées sur ce site est
                        l'association <strong class="text-text">LIBRE ÉCOLE RUDOLF STEINER</strong>
                        (École Steiner Waldorf de Verrières-le-Buisson), déclarée auprès de la
                        préfecture d'Évry sous le numéro <strong class="text-text">W913000590</strong>,
                        dont le siège social est situé
                        <strong class="text-text">62 rue de Paris, 91370 Verrières-le-Buisson</strong>,
                        représentée par <strong class="text-text">Lionel Cornec, Trésorier</strong>.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Pour toute question relative à vos données personnelles, vous pouvez
                        contacter <strong class="text-text">cercledeconfiancevlb@gmail.com</strong>,
                        ou joindre l'établissement au
                        <strong class="text-text">01 60 11 38 12</strong> / <strong class="text-text">accueil@ecole-steiner-verrieres.org</strong>.
                    </p>
                </div>

                {{-- Données collectées --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="document-text" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Données que nous collectons') }}</h3>
                    </div>

                    <p class="mb-4 leading-[1.8] text-text-muted">
                        <strong class="text-text">Formulaire « Nous contacter ».</strong> Le nom et
                        l'adresse email sont <strong class="text-text">facultatifs</strong> — vous
                        n'êtes jamais obligé de les renseigner pour déposer un témoignage. Le contenu
                        du message est toujours enregistré. Un code de suivi (ex.
                        <code class="rounded bg-surface-muted px-1.5 py-0.5 text-sm">ABCD-EFGH</code>)
                        vous est remis pour consulter les réponses ; il n'est conservé nulle part par
                        l'application après vous avoir été communiqué.
                    </p>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        <strong class="text-text">Comptes des membres de l'équipe</strong> (parents,
                        professeurs, administrateurs habilités) : nom, adresse email, mot de passe
                        (jamais stocké en clair), photo facultative, rôle et titre au sein du Cercle,
                        et — si activée — la configuration de la double authentification.
                    </p>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        <strong class="text-text">Contenu des dossiers.</strong> Les messages échangés
                        entre un expéditeur et l'équipe, ainsi que les notes internes ajoutées à un
                        dossier, sont enregistrés pour assurer le suivi du signalement.
                    </p>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        <strong class="text-text">Chiffrement.</strong> Le nom et l'email de
                        l'expéditeur sont chiffrés avec l'algorithme <strong class="text-text">AES-256-CBC</strong>
                        (authentifié par HMAC-SHA256) ; les messages échangés et les notes internes
                        sont chiffrés avec l'algorithme <strong class="text-text">AES-256-GCM</strong>,
                        avec une clé de chiffrement propre à chaque dossier.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        <strong class="text-text">Cookies techniques.</strong> Un cookie mémorise que
                        le code d'accès de l'établissement a été saisi (valable 30 jours) et un
                        cookie de session assure le bon fonctionnement du site pendant votre visite.
                        Aucun cookie publicitaire ou de mesure d'audience n'est utilisé. Les polices
                        de caractères sont chargées depuis les serveurs de Google Fonts, ce qui
                        transmet votre adresse IP à Google lors de l'affichage d'une page.
                    </p>
                </div>

                {{-- Pourquoi et sur quelle base --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="scale" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Pourquoi, et sur quelle base légale') }}</h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Ces données sont traitées pour permettre le fonctionnement du Cercle de
                        Confiance : recevoir et traiter les signalements, en assurer le suivi, et
                        gérer les comptes des personnes habilitées à y accéder. Ce traitement repose
                        sur l'intérêt légitime de l'établissement à assurer un espace d'écoute et de
                        médiation pour sa communauté, et, pour les données facultatives (nom, email de
                        l'expéditeur), sur votre consentement explicite au moment où vous choisissez
                        de les renseigner.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Le contenu des signalements peut, selon les cas, concerner des mineurs. Il est
                        traité avec une vigilance renforcée et n'est accessible qu'aux personnes
                        explicitement autorisées à consulter le dossier concerné (voir ci-dessous).
                    </p>
                </div>

                {{-- Sécurité --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="shield-check" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Comment vos données sont protégées') }}</h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Le contenu des dossiers (messages échangés et notes internes) est
                        <strong class="text-text">chiffré</strong> : chaque dossier possède sa propre
                        clé de chiffrement, elle-même remise uniquement aux personnes explicitement
                        autorisées à consulter ce dossier précis. Un membre de l'équipe ne peut donc
                        lire que les dossiers qui lui ont été attribués ou partagés — jamais
                        l'ensemble des dossiers par défaut.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Les mots de passe des comptes ne sont jamais stockés en clair, et une double
                        authentification peut être activée pour les comptes administrateurs.
                    </p>
                </div>

                {{-- Qui y a accès --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="user-group" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Qui a accès à vos données') }}</h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Un dossier n'est visible que par les membres de l'équipe (parents, professeurs) auxquels il a été explicitement attribué ou partagé.
                        Aucune donnée n'est vendue ni transmise à des tiers à des fins commerciales.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Les données sont hébergées par <strong class="text-text">OVH</strong>, dont
                        le siège social est situé
                        <strong class="text-text">2 rue Kellermann, 59053 Roubaix</strong> (France).
                    </p>
                </div>

                {{-- Durée de conservation --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="clock" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Combien de temps vos données sont conservées') }}</h3>
                    </div>
                    <p class="mb-4 leading-[1.8] text-text-muted">
                        Un dossier clos (statut « archivé ») est <strong class="text-text">supprimé
                        automatiquement et définitivement 6 mois après sa clôture</strong> — dossier,
                        messages, notes internes et clés de chiffrement associées sont effacés dans
                        leur intégralité. Si le dossier est rouvert avant ce délai, le compteur de 6
                        mois est annulé.
                    </p>
                    <p class="leading-[1.8] text-text-muted">
                        Les comptes des membres qui quittent l'établissement sont supprimés aussitôt après
                        leur départ, à la demande du responsable du traitement.
                    </p>
                </div>

                {{-- Vos droits --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="hand-raised" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Vos droits') }}</h3>
                    </div>
                    <p class="mb-6 leading-[1.8] text-text-muted">
                        Conformément au Règlement général sur la protection des données (RGPD) et à
                        la loi Informatique et Libertés, vous disposez des droits suivants sur vos
                        données personnelles :
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Droit d\'accès') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Savoir quelles données vous concernant sont enregistrées.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Droit de rectification') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Faire corriger une donnée inexacte ou incomplète.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Droit à l\'effacement') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Demander la suppression de vos données, sous réserve des obligations légales de conservation.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Droit d\'opposition') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Vous opposer à un traitement pour un motif légitime.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Droit à la limitation') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Demander la mise en pause temporaire d\'un traitement.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl bg-surface-muted p-4">
                            <flux:icon name="check-circle" class="size-5 shrink-0 text-secondary-500" />
                            <div>
                                <strong class="text-text">{{ __('Droit à la portabilité') }}</strong>
                                <p class="mt-1 text-sm leading-relaxed text-text-muted">
                                    {{ __('Recevoir vos données dans un format réutilisable.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-6 leading-[1.8] text-text-muted">
                        Un expéditeur anonyme ne pouvant pas être identifié, ces droits s'exercent
                        alors en fournissant le code de suivi de son dossier. Un membre de l'équipe
                        disposant d'un compte peut à tout moment consulter, corriger ou supprimer les
                        informations de son profil, ou demander la suppression de son compte, depuis
                        son espace personnel ou en contactant le responsable du traitement.
                    </p>
                </div>

                {{-- Réclamation --}}
                <div class="mb-16 rounded-2xl border border-border bg-surface p-8 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500/10 to-primary-500/10">
                            <flux:icon name="flag" class="size-6 text-primary-500" />
                        </div>
                        <h3 class="font-display text-2xl text-text">{{ __('Une question, une réclamation ?') }}</h3>
                    </div>
                    <p class="leading-[1.8] text-text-muted">
                        Pour exercer l'un de ces droits, ou pour toute question sur l'utilisation de
                        vos données, contactez <strong class="text-text">cercledeconfiancevlb@gmail.com</strong>.
                        Si vous estimez, après nous avoir contactés, que vos droits ne
                        sont pas respectés, vous pouvez introduire une réclamation auprès de la
                        <flux:link href="https://www.cnil.fr/fr/plaintes" class="text-primary-500">
                            Commission nationale de l'informatique et des libertés (CNIL)
                        </flux:link>.
                    </p>
                </div>

                {{-- CTA --}}
                <div class="mt-16 rounded-2xl border border-border bg-gradient-to-br from-surface-muted to-surface p-10 text-center">
                    <h4 class="mb-3 font-display text-xl text-text">
                        {{ __('Une question sur vos données ?') }}
                    </h4>
                    <p class="mb-6 text-text-muted">
                        {{ __('N\'hésitez pas à nous contacter, en toute confidentialité.') }}
                    </p>
                    <flux:button
                        :href="route('contact.show')"
                        variant="primary"
                        icon:trailing="arrow-right"
                        wire:navigate
                        class="[--color-accent:var(--color-primary-500)] [--color-accent-content:var(--color-primary-500)] [--color-accent-foreground:var(--color-surface)]"
                    >
                        {{ __('Nous contacter') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
