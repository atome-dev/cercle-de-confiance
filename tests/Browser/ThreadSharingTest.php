<?php

use App\Actions\CreateThreadWithMessage;
use App\Models\User;

it('lets a parent share a thread with a professeur through the pillbox picker', function () {
    $parent = User::factory()->parent()->create(['name' => 'Nicolas Parent']);
    $professeur = User::factory()->professeur()->create(['name' => 'Julie Professeur']);

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];

    $page = visit(route('login'));

    $page->fill('email', $parent->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page->navigate(route('threads.show', $thread));

    $page->assertSee('Dossier '.$thread->code);

    // Selecting an option in the pillbox and submitting the "share" form is
    // what previously crashed the app with a TypeError, because Livewire
    // could not assign the pillbox's value to the array-typed
    // ThreadShow::$shareUserIds property (see flux:pillbox `multiple` prop
    // in thread-show.blade.php). Clicking the real submit button here
    // deadlocks the single-process browser test server, so the form is
    // submitted directly via requestSubmit() instead.
    $page->click('Choisir une ou plusieurs personnes…')
        ->click('Julie Professeur');

    $page->script('document.querySelector("form[wire\\\\:submit=share]").requestSubmit()');

    $page->assertSee('partagé par Nicolas Parent')
        ->assertNoJavaScriptErrors();

    expect($thread->fresh()->isAccessibleBy($professeur))->toBeTrue();
});
