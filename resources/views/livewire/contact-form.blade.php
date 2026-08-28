<div class="max-w-2xl mx-auto p-6">
    @if ($generatedFullCode)
        <flux:callout variant="success" icon="check-circle" heading="Message envoyé avec succès">
            <p class="mb-2">
                Conservez précieusement ce code : il vous permettra de suivre les échanges
                concernant votre dossier sans avoir besoin de créer de compte.
            </p>
            <div class="flex items-center gap-3 mt-4">
                <flux:heading size="xl" class="font-mono tracking-widest">
                    {{ $generatedFullCode }}
                </flux:heading>
                <flux:button
                    icon="clipboard"
                    size="sm"
                    x-on:click="navigator.clipboard.writeText('{{ $generatedFullCode }}')"
                >
                    Copier
                </flux:button>
            </div>
        </flux:callout>

        <div class="mt-6">
            <flux:button href="{{ route('contact.show') }}" variant="ghost" wire:navigate>
                Envoyer un nouveau message
            </flux:button>
        </div>
    @else
        <flux:heading size="xl" class="mb-6">Contacter la commission</flux:heading>

        <form wire:submit="submit" class="space-y-6">
            <flux:radio.group wire:model.live="recipientType" label="Destinataire">
                <flux:radio value="group" label="Toute la commission" />
                <flux:radio value="member" label="Un membre en particulier" />
            </flux:radio.group>

            @if ($recipientType === 'member')
                <flux:select wire:model="recipientUserId" label="Choisir un membre" placeholder="Sélectionner…">
                    @foreach ($members as $member)
                        <flux:select.option value="{{ $member->id }}">{{ $member->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <flux:input wire:model="senderName" label="Votre nom" />
            <flux:input wire:model="senderEmail" label="Votre email" type="email" />

            <flux:textarea wire:model="message" label="Votre message" rows="6" />

            @auth
                <flux:checkbox wire:model="sendAnonymously" label="Envoyer ce message de façon anonyme" />
            @endauth

            <flux:button type="submit" variant="primary" icon="paper-airplane">
                Envoyer le message
            </flux:button>
        </form>
    @endif
</div>
