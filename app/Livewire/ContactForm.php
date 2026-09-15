<?php

namespace App\Livewire;

use App\Actions\CreateThreadWithMessage;
use App\Enums\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts::public')]
#[Title('Nous contacter')]
class ContactForm extends Component
{
    #[Validate('string|max:255')]
    public string $senderName = '';

    #[Validate('email|max:255')]
    public string $senderEmail = '';

    #[Validate('required|string|min:10|max:5000')]
    public string $message = '';

    #[Validate('in:group,member')]
    public string $recipientType = 'group';

    public ?int $recipientUserId = null;

    public bool $sendAnonymously = false;

    public ?string $generatedFullCode = null;

    public function updatedRecipientType(): void
    {
        if ($this->recipientType === 'group') {
            $this->recipientUserId = null;
        }
    }

    public function rules(): array
    {
        return [
            'recipientUserId' => $this->recipientType === 'member'
                ? 'required|exists:users,id'
                : 'nullable',
        ];
    }

    public function submit(CreateThreadWithMessage $action): void
    {
        $this->validate();

        $result = $action->execute(
            senderName: $this->senderName,
            senderEmail: $this->senderEmail,
            message: $this->message,
            recipientType: $this->recipientType,
            recipientUserId: $this->recipientUserId
        );

        if ($result['fullCode']) {
            $this->generatedFullCode = $result['fullCode'];
            $this->reset('senderName', 'senderEmail', 'message');
        } else {
            $this->redirect(route('threads.show', $result['thread']), navigate: true);
        }
    }

    public function members()
    {
        return User::role([Role::Parent, Role::Professeur])
            ->orderBy('name')
            ->get(['id', 'name', 'membre_role', 'membre_titre']);
    }

    public function render()
    {
        return view('livewire.contact-form', [
            'members' => $this->members(),
        ]);
    }
}
