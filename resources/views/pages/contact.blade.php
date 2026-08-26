<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

new #[Layout('layouts::public')] #[Title('Nous contacter')] class extends Component {

    use WithFileUploads;

    public string $mode = 'identify'; // identify | anonymous

    public string $nom = '';
    public string $courriel = '';
    public string $telephone = '';

    public string $destinataire = 'all'; // all | member
    public ?string $selectedMember = null;

    public string $sujet = '';
    public string $message = '';
    public $pieceJointe = null;

    public bool $showSuccessModal = false;
    public string $caseNumber = '';

    public array $membres = [
        'celeste-edouard' => "Céleste Édouard — Parent d'élève",
        'brigitte-loze' => 'Brigitte Lozé — Professeure',
        'boniface-malet' => "Boniface Malet — Parent d'élève",
        'marc-antoine-dubuisson' => 'Marc-Antoine Dubuisson — Salarié',
        'mathis-thibodeau' => 'Mathis Thibodeau — Professeur',
        'gaspard-matthieu' => "Gaspard Matthieu — Parent d'élève",
        'godelieve-bachelot' => "Godeliève Bachelot — Parent d'élève",
        'pauline-jaubert' => 'Pauline Jaubert — Professeure',
    ];

    protected function rules(): array
    {
        $rules = [
            'destinataire' => 'required|in:all,member',
            'sujet' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'pieceJointe' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ];

        if ($this->mode === 'identify') {
            $rules['nom'] = 'required|string|max:255';
            $rules['courriel'] = 'required|email';
            $rules['telephone'] = 'nullable|string|max:20';
        }

        if ($this->destinataire === 'member') {
            $rules['selectedMember'] = 'required|string';
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate();

        // TODO: chiffrement + persistance en base + génération du lien unique

        $this->caseNumber = strtoupper(Str::random(6));

        $this->showSuccessModal = true;

        $this->reset([
            'nom', 'courriel', 'telephone',
            'destinataire', 'selectedMember',
            'sujet', 'message', 'pieceJointe',
        ]);
        $this->destinataire = 'all';
        $this->mode = 'identify';
    }

    public function closeModal(): void
    {
        $this->showSuccessModal = false;
        $this->caseNumber = '';
    }
}
?>

<div>
    <section>
        {{-- Titre de la page --}}
        <div class="text-center py-16 space-y-4">
            <flux:heading size="xl" level="1">Nous Contacter</flux:heading>
            <flux:text size="lg" class="text-zinc-500 max-w-2xl mx-auto">
                Que vous soyez élève, parent ou salarié, vous pouvez nous solliciter pour toute difficulté
                ou conflit.
            </flux:text>
        </div>

        {{-- Formulaire --}}
        <div class="max-w-3xl mx-auto pb-16 space-y-8">

            <flux:callout icon="lock-closed" color="zinc">
                <flux:callout.heading>Confidentialité garantie</flux:callout.heading>
                <flux:callout.text>
                    Remplissez le formulaire ci-dessous pour nous transmettre votre demande. Vous pouvez choisir
                    de rester anonyme ou de nous fournir vos coordonnées pour un suivi personnalisé. Toutes les
                    demandes sont traitées avec la plus stricte confidentialité.
                    <br><br>
                    🔒 Tous les échanges sont chiffrés et ne peuvent être lus que par le lien qui vous sera
                    communiqué et transmis exclusivement à vous et le(s) destinataire(s).
                </flux:callout.text>
            </flux:callout>

            <flux:card class="space-y-10">
                <form wire:submit="submit" class="space-y-10">

                    {{-- Section 1 : Identité --}}
                    <div class="space-y-6">
                        <flux:heading size="lg" class="flex items-center gap-2">
                            <flux:badge size="sm" color="blue" circle>1</flux:badge>
                            Votre identité
                        </flux:heading>

                        <flux:radio.group wire:model.live="mode" variant="segmented">
                            <flux:radio value="identify" label="M'identifier" />
                            <flux:radio value="anonymous" label="Rester anonyme" />
                        </flux:radio.group>

                        @if ($mode === 'identify')
                            <div class="grid gap-4 sm:grid-cols-2">
                                <flux:input
                                    wire:model="nom"
                                    label="Nom et prénom"
                                    placeholder="Marie Dupont"
                                />

                                <flux:input
                                    wire:model="courriel"
                                    type="email"
                                    label="Courriel"
                                    placeholder="marie.dupont@exemple.fr"
                                />

                                <flux:input
                                    wire:model="telephone"
                                    type="tel"
                                    label="Téléphone (facultatif)"
                                    placeholder="06 12 34 56 78"
                                    class="sm:col-span-2"
                                />
                            </div>
                        @else
                            <flux:callout icon="information-circle" color="blue">
                                <flux:callout.text>
                                    📋 Vous recevrez un numéro de dossier unique pour suivre votre demande sans
                                    avoir à vous reconnecter.
                                </flux:callout.text>
                            </flux:callout>
                        @endif
                    </div>

                    <flux:separator />

                    {{-- Section 2 : Destinataire --}}
                    <div class="space-y-6">
                        <flux:heading size="lg" class="flex items-center gap-2">
                            <flux:badge size="sm" color="blue" circle>2</flux:badge>
                            Destinataire
                        </flux:heading>

                        <flux:radio.group wire:model.live="destinataire">
                            <flux:radio value="all" label="L'ensemble du Cercle de Confiance" />
                            <flux:radio value="member" label="Un membre en particulier" />
                        </flux:radio.group>

                        <flux:text size="sm" class="text-zinc-500">
                            Vous pouvez choisir un membre spécifique en cas de conflit d'intérêt avec un autre
                            membre.
                        </flux:text>

                        @if ($destinataire === 'member')
                            <flux:select
                                wire:model="selectedMember"
                                label="Sélectionnez le membre"
                                placeholder="-- Choisissez un membre --"
                            >
                                @foreach ($membres as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                    </div>

                    <flux:separator />

                    {{-- Section 3 : Message --}}
                    <div class="space-y-6">
                        <flux:heading size="lg" class="flex items-center gap-2">
                            <flux:badge size="sm" color="blue" circle>3</flux:badge>
                            Votre message
                        </flux:heading>

                        <flux:input
                            wire:model="sujet"
                            label="Sujet"
                            placeholder="Décrivez brièvement le sujet de votre demande"
                        />

                        <flux:textarea
                            wire:model="message"
                            label="Message"
                            rows="6"
                            placeholder="Décrivez votre situation ou votre demande en détail. Toutes les informations que vous partagerez resteront confidentielles."
                        />

                        <flux:field>
                            <flux:label>Pièce jointe (facultatif)</flux:label>
                            <input
                                type="file"
                                wire:model="pieceJointe"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="block w-full text-sm border border-zinc-200 dark:border-zinc-700 rounded-lg p-3 cursor-pointer"
                            />
                            <div wire:loading wire:target="pieceJointe" class="text-sm text-zinc-500">
                                Chargement en cours...
                            </div>
                            @if ($pieceJointe)
                                <flux:text size="sm" class="text-green-600">
                                    Fichier sélectionné : {{ $pieceJointe->getClientOriginalName() }}
                                </flux:text>
                            @endif
                            <flux:error name="pieceJointe" />
                        </flux:field>
                    </div>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary">
                            Envoyer ma demande
                        </flux:button>
                    </div>

                </form>
            </flux:card>
        </div>
    </section>
    <flux:separator />

    {{-- Modal de succès --}}
    <flux:modal wire:model="showSuccessModal" name="success-modal" class="max-w-md text-center space-y-6">
        <div class="flex justify-center">
            <flux:icon.check-circle class="w-16 h-16 text-green-500" />
        </div>

        <flux:heading size="xl">Votre demande a bien été transmise</flux:heading>

        <flux:card class="bg-zinc-50 dark:bg-zinc-800 space-y-2 text-center">
            <flux:text size="sm" class="text-zinc-500">Votre numéro de dossier</flux:text>
            <flux:heading size="lg" class="tracking-widest font-mono">
                {{ $caseNumber }}
            </flux:heading>
            <flux:button
                size="sm"
                variant="ghost"
                icon="clipboard"
                x-on:click="navigator.clipboard.writeText('{{ $caseNumber }}')"
            >
                Copier
            </flux:button>
        </flux:card>

        <flux:text class="text-zinc-500">
            Conservez précieusement ce numéro pour suivre l'avancement de votre dossier.
        </flux:text>

        <flux:button variant="ghost" wire:click="closeModal">
            Fermer
        </flux:button>
    </flux:modal>
</div>
