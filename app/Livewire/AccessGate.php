<?php

namespace App\Livewire;

use App\Http\Middleware\EnsureAccessCodeIsValid;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Code d\'accès')]
class AccessGate extends Component
{
    public string $code = '';

    /**
     * Attempt to unlock the public pages with the shared access code.
     */
    public function attempt(): void
    {
        $this->validate([
            'code' => ['required', 'string'],
        ]);

        $this->code = Str::upper($this->code);

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

    public function render()
    {
        return view('livewire.access-gate');
    }
}
