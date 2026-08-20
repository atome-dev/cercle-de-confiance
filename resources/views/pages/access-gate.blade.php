<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Code d\'accès')] class extends Component {
    public string $code = '';

    /**
     * Attempt to unlock the public pages with the shared access code.
     */
    public function attempt(): void
    {
        $this->validate([
            'code' => ['required', 'string'],
        ]);

        if (RateLimiter::tooManyAttempts($this->throttleKey(), 6)) {
            $this->addError('code', __('Trop de tentatives. Réessayez dans :seconds secondes.', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
            ]));

            return;
        }

        if (! hash_equals((string) config('access.code'), $this->code)) {
            RateLimiter::hit($this->throttleKey(), 60);

            $this->addError('code', __('Code d\'accès incorrect.'));

            return;
        }

        RateLimiter::clear($this->throttleKey());

        Cookie::queue(
            'access_granted',
            EnsureAccessCodeIsValid::expectedCookieValue(),
            60 * 24 * 30,
            null,
            null,
            config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax'),
        );

        $this->redirectRoute('home', navigate: true);
    }

    /**
     * The rate limiter key for the current requester.
     */
    protected function throttleKey(): string
    {
        return 'access-code:'.request()->ip();
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Accès protégé')" :description="__('Saisissez le code d\'accès communiqué dans l\'établissement.')" />

    <form wire:submit="attempt" class="flex flex-col gap-6">
        <div>
            <flux:input
                wire:model="code"
                :label="__('Code d\'accès')"
                type="text"
                required
                autofocus
                autocomplete="off"
            />
            <flux:error name="code" />
        </div>

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Valider') }}
        </flux:button>
    </form>
</div>
