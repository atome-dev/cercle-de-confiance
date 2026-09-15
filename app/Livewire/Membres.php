<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Nos membres')]
class Membres extends Component
{
    public bool $showModal = false;

    public ?User $editing = null;

    public string $name = '';

    public string $membre_titre = '';

    public string $membre_role = 'parent';

    public ?string $photo = null;

    public string $email = '';

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function membres(): Collection
    {
        return User::role([Role::Parent, Role::Professeur])
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->reset(['editing', 'name', 'membre_titre', 'photo', 'email']);
        $this->membre_role = 'parent';
        $this->showModal = true;
    }

    public function edit(User $membre): void
    {
        $this->editing = $membre;
        $this->name = $membre->name;
        $this->membre_titre = (string) $membre->membre_titre;
        $this->membre_role = (string) $membre->membre_role;
        $this->photo = $membre->photo;
        $this->email = $membre->email;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'membre_titre' => ['required', 'string', 'max:255'],
            'membre_role' => ['required', 'string', 'in:parent,professeur'],
            'photo' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editing)],
        ]);

        // User::$fillable only allows mass-assigning name/email/password, so
        // the membre_* columns and password/email_verified_at are set
        // individually rather than through create()/update().
        $user = $this->editing ?? new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->membre_titre = $validated['membre_titre'];
        $user->membre_role = $validated['membre_role'];
        $user->photo = $validated['photo'];

        if (! $this->editing) {
            $user->password = Hash::make(Str::random(32));
            $user->email_verified_at = now();
        }

        $user->save();
        $user->syncRoles([$validated['membre_role']]);

        $this->showModal = false;
        unset($this->membres);
    }

    public function delete(User $membre): void
    {
        $membre->delete();

        unset($this->membres);
    }

    /**
     * Tailwind classes are written as full literal strings (not interpolated)
     * so the JIT scanner can pick them up statically.
     */
    public function avatarClasses(?string $role): string
    {
        return match ($role) {
            'parent' => '!bg-role-parent !text-white',
            'professeur' => '!bg-role-professeur !text-white',
            default => '',
        };
    }

    public function badgeClasses(?string $role): string
    {
        return match ($role) {
            'parent' => 'bg-role-parent/10 text-role-parent',
            'professeur' => 'bg-role-professeur/10 text-role-professeur',
            default => 'bg-surface-muted text-text-muted',
        };
    }

    public function render()
    {
        return view('livewire.membres');
    }
}
