<div class="max-w-2xl mx-auto px-4 py-10 sm:px-6">
    @if ($generatedFullCode)
        {{-- État : Message envoyé --}}
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                <flux:icon name="check-circle" class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="xl">Message envoyé avec succès</flux:heading>
            <flux:text class="mt-2 text-zinc-500">
                La commission a bien reçu votre message et reviendra vers vous prochainement.
            </flux:text>
        </div>

        <flux:callout variant="success" icon="information-circle" heading="Votre code de suivi">
            <p class="mb-4">
                Conservez précieusement ce code : il vous permettra de suivre les échanges
                concernant votre dossier.
            </p>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-2">
                <div class="flex-1 rounded-lg border border-dashed border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-950/40 px-4 py-3 text-center">
                    <flux:heading size="xl" class="font-mono tracking-[0.3em] text-green-700 dark:text-green-300">
                        {{ $generatedFullCode }}
                    </flux:heading>
                </div>
                <flux:button
                    icon="clipboard"
                    variant="filled"
                    x-data
                    x-on:click="
                        navigator.clipboard.writeText('{{ $generatedFullCode }}');
                        $flux.toast('Code copié dans le presse-papiers', { variant: 'success' });
                    "
                >
                    Copier
                </flux:button>
            </div>
        </flux:callout>

        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
            <flux:button href="{{ route('anonymous-access') }}" variant="primary" icon="arrow-right-circle" wire:navigate>
                Accéder à mon dossier
            </flux:button>
            <flux:button href="{{ route('contact.show') }}" variant="ghost" icon="arrow-path" wire:navigate>
                Envoyer un nouveau message
            </flux:button>
        </div>
    @else
        {{-- État : Formulaire --}}
        <div class="mb-8 text-center">
            <flux:heading size="xl">Contacter le Cercle de Confiance</flux:heading>
            <flux:text class="mt-2 text-zinc-500">
                Lorsque vous vous adressez au Cercle de Confiance, seuls les parents recevront votre message afin de s'assurer qu'il n'y a pas de conflit d'intérêt avec un professeur.
                Vous recevrez un code de suivi pour consulter les réponses.
            </flux:text>
        </div>

        <flux:card class="p-6 sm:p-8">
            <form wire:submit="submit" class="space-y-8">
                {{-- Destinataire --}}
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wide">
                        Destinataire
                    </flux:heading>

                    <flux:radio.group wire:model.live="recipientType" variant="cards" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <flux:radio value="group" label="Parents du Cercle de Confiance" icon="user-group" />
                        <flux:radio value="member" label="Un membre en particulier" icon="user" />
                    </flux:radio.group>

                    @if ($recipientType === 'member')
                        <flux:select wire:model="recipientUserId" label="Choisir un membre" placeholder="Sélectionner un membre...">
                            @foreach ($members as $member)
                                <flux:select.option value="{{ $member->id }}">
                                    {{ $member->name }} — {{ $member->membre_titre }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif
                </div>
                <flux:separator />

                {{-- Coordonnées --}}
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wide">
                        Vos coordonnées
                    </flux:heading>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="senderName" label="Votre nom" icon="user" />
                        <flux:input wire:model="senderEmail" label="Votre email" type="email" icon="envelope" />
                    </div>

                    <flux:checkbox
                        wire:model="sendAnonymously"
                        label="Je préfère pour le moment rester anonyme"
                        class="items-center"
                    />

                </div>

                <flux:separator />

                {{-- Message --}}
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wide">
                        Votre message
                    </flux:heading>

                    <flux:textarea
                        wire:model="message"
                        label="Décrivez votre demande"
                        placeholder="Écrivez votre message ici..."
                        rows="6"
                    />
                </div>



                <div class="pt-2 flex justify-end">
                    <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full sm:w-auto">
                        Envoyer le message
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif
</div>
