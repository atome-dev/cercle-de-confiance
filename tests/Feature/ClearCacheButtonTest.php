<?php

use App\Livewire\ClearCacheButton;
use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;

test('clearing the cache also forgets every cookie sent by the browser when debug is enabled', function () {
    config(['app.debug' => true]);

    Livewire::withCookies(['access_granted' => 'some-value', 'locale' => 'fr'])
        ->test(ClearCacheButton::class)
        ->call('clearCache');

    expect(Cookie::hasQueued('access_granted'))->toBeTrue()
        ->and(Cookie::queued('access_granted')->getValue())->toBeNull()
        ->and(Cookie::hasQueued('locale'))->toBeTrue()
        ->and(Cookie::queued('locale')->getValue())->toBeNull();
});

test('clearing the cache does nothing when debug is disabled', function () {
    config(['app.debug' => false]);

    Livewire::withCookies(['access_granted' => 'some-value'])
        ->test(ClearCacheButton::class)
        ->call('clearCache');

    expect(Cookie::hasQueued('access_granted'))->toBeFalse();
});
