<div>
    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Cartouches') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Gestion des cartouches affichés dans la section') }}
                    <br>
                    {{ __('"Que faisons-nous ?"') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Cartouches table --}}
    <section class="py-24">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mb-8 flex justify-end">
                <flux:button variant="primary" wire:click="create">
                    {{ __('Ajouter une cartouche') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Position') }}</flux:table.column>
                    <flux:table.column>{{ __('Icône') }}</flux:table.column>
                    <flux:table.column>{{ __('Titre') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->cartouches as $cartouche)
                        <flux:table.row wire:key="cartouche-{{ $cartouche->id }}">
                            <flux:table.cell>{{ $cartouche->position }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:icon :name="$cartouche->icone->value" class="size-6 text-primary-500" />
                            </flux:table.cell>
                            <flux:table.cell>{{ $cartouche->titre }}</flux:table.cell>
                            <flux:table.cell class="description-cell w-auto">
                                {{ $cartouche->description }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" wire:click="edit({{ $cartouche->id }})">
                                        {{ __('Modifier') }}
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        wire:click="delete({{ $cartouche->id }})"
                                        wire:confirm="{{ __('Supprimer cette cartouche ?') }}"
                                    >
                                        {{ __('Supprimer') }}
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </section>

    {{-- Create / edit modal --}}
    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editing ? __('Modifier la cartouche') : __('Ajouter une cartouche') }}
                </flux:heading>
            </div>

            <flux:select :label="__('Icône')" wire:model="icone" :placeholder="__('Choisir une icône...')">
                @foreach (\App\Enums\CartoucheIcone::cases() as $iconeOption)
                    <flux:select.option value="{{ $iconeOption->value }}">{{ $iconeOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input :label="__('Titre')" wire:model="titre" />
            <flux:textarea :label="__('Description')" wire:model="description" rows="7" />
            <flux:input
                type="number"
                min="1"
                :label="__('Position')"
                wire:model="position"
                :description="__('Ordre d\'affichage sur l\'accueil : 1 apparaît en premier.')"
            />

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Annuler') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Enregistrer') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
