<?php

use App\Models\User;

test('the parent factory state assigns the "parent" role', function () {
    $user = User::factory()->parent()->create();

    expect($user->hasRole('parent'))->toBeTrue();
});

test('the professeur factory state assigns the "professeur" role', function () {
    $user = User::factory()->professeur()->create();

    expect($user->hasRole('professeur'))->toBeTrue();
});
