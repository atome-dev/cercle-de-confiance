<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Politique de conservation RGPD : voir /rgpd et PurgeArchivedThreads.
Schedule::command('app:purge-archived-threads')->daily();
