<div>
    {{-- Page title --}}
    <section class="bg-gradient-to-b from-surface-muted to-surface py-16 text-center">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 font-display text-4xl text-text sm:text-5xl">{{ __('Nos membres') }}</h1>
                <p class="mx-auto max-w-xl text-xl text-text-muted">
                    {{ __('Découvrez les personnes qui composent le Cercle de Confiance.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Members table --}}
    <section class="py-24">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-12">
            <div class="mb-8 flex justify-end">
                <flux:button variant="primary" wire:click="create">
                    {{ __('Ajouter un membre') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nom') }}</flux:table.column>
                    <flux:table.column>{{ __('Titre') }}</flux:table.column>
                    <flux:table.column>{{ __('Rôle') }}</flux:table.column>
                    <flux:table.column>{{ __('Courriel') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->membres as $membre)
                        <flux:table.row wire:key="membre-{{ $membre->id }}">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar
                                    :name="$membre->name"
                                    :src="$membre->photo_url"
                                    size="xl"
                                    circle
                                    class="{{ $this->avatarClasses($membre->membre_role) }}"
                                />
                                {{ $membre->name }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $membre->membre_titre }}</flux:table.cell>
                            @php($isAdmin = $membre->hasRole(\App\Enums\Role::Administrateur))
                            <flux:table.cell>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase {{ $this->badgeClasses($membre->membre_role) }}">
                                        {{ $membre->membre_role }}
                                    </span>
                                    @if ($isAdmin)
                                        <flux:badge color="amber" size="sm" icon="shield-check" rounded>
                                            {{ __('Admin') }}
                                        </flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $membre->email }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:tooltip :content="__('Modifier')">
                                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $membre->id }})" />
                                    </flux:tooltip>

                                    @if (! $isAdmin)
                                        <flux:tooltip :content="__('Nommer administrateur')">
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="shield-check"
                                                wire:click="toggleAdmin({{ $membre->id }})"
                                                wire:confirm="{{ __('Nommer :name administrateur ?', ['name' => $membre->name]) }}"
                                            />
                                        </flux:tooltip>
                                    @elseif ($membre->is(auth()->user()))
                                        <flux:tooltip :content="__('Vous ne pouvez pas retirer votre propre rôle administrateur')">
                                            <div>
                                                <flux:button size="sm" variant="ghost" icon="shield-check" icon:variant="solid" class="!text-amber-500" disabled />
                                            </div>
                                        </flux:tooltip>
                                    @else
                                        <flux:tooltip :content="__('Retirer le rôle administrateur')">
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="shield-check"
                                                icon:variant="solid"
                                                class="!text-amber-500"
                                                wire:click="toggleAdmin({{ $membre->id }})"
                                                wire:confirm="{{ __('Retirer le rôle administrateur à :name ?', ['name' => $membre->name]) }}"
                                            />
                                        </flux:tooltip>
                                    @endif

                                    <flux:tooltip :content="__('Supprimer')">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            class="!text-red-500"
                                            wire:click="delete({{ $membre->id }})"
                                            wire:confirm="{{ __('Supprimer ce membre ?') }}"
                                        />
                                    </flux:tooltip>
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
                    {{ $editing ? __('Modifier le membre') : __('Ajouter un membre') }}
                </flux:heading>
            </div>

            <flux:input :label="__('Nom')" wire:model="name" />
            <flux:input :label="__('Titre')" wire:model="membre_titre" />

            <flux:select :label="__('Rôle')" wire:model="membre_role">
                <flux:select.option value="parent">{{ __("Parent d'élève") }}</flux:select.option>
                <flux:select.option value="professeur">{{ __('Professeur') }}</flux:select.option>
            </flux:select>

            <flux:input :label="__('Photo')" wire:model="photo" :description="__('Nom du fichier dans storage/membres, exemple : nom-prenom.jpg')" />
            <flux:input :label="__('Courriel')" type="email" wire:model="email" :description="$editing ? null : __('Un mot de passe aléatoire est généré ; le membre devra utiliser « mot de passe oublié » pour se connecter.')" />

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
