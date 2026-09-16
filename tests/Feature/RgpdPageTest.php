<?php

use App\Console\Commands\PurgeArchivedThreads;
use App\Http\Middleware\EnsureAccessCodeIsValid;
use Database\Seeders\RoleSeeder;

test('the rgpd page is accessible without the access cookie', function () {
    $response = $this->get(route('rgpd.show'));

    $response->assertOk();
    $response->assertSeeText('Protection des données personnelles');
    $response->assertSeeText('Responsable du traitement');
    $response->assertSeeText('Données que nous collectons');
    $response->assertSeeText('Vos droits');
    $response->assertSeeText('AES-256-CBC');
    $response->assertSeeText('AES-256-GCM');
});

test('the stated retention period matches the purge command\'s actual retention window', function () {
    $response = $this->get(route('rgpd.show'));

    $response->assertOk();
    $response->assertSeeText(PurgeArchivedThreads::RETENTION_MONTHS.' mois après sa clôture');
});

test('the rgpd page is linked from the footer', function () {
    $this->seed(RoleSeeder::class);

    $response = $this->withCookie('access_granted', EnsureAccessCodeIsValid::expectedCookieValue())
        ->get(route('home'));

    $response->assertOk();
    $response->assertSee(route('rgpd.show'));
});
