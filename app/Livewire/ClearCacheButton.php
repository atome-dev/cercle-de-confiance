<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class ClearCacheButton extends Component
{
    public ?string $message = null;

    public bool $isClearing = false;

    public function clearCache(): void
    {
        if (! config('app.debug')) {
            return;
        }

        $this->isClearing = true;

        try {
            Artisan::call('optimize:clear');

            foreach (request()->cookies->keys() as $cookieName) {
                Cookie::queue(Cookie::forget($cookieName));
            }

            $this->message = 'Cache et cookies vidés avec succès !';
        } catch (\Throwable $e) {
            $this->message = 'Erreur lors du vidage du cache : '.$e->getMessage();
        }

        $this->isClearing = false;
    }

    public function render()
    {
        return view('livewire.clear-cache-button');
    }
}
