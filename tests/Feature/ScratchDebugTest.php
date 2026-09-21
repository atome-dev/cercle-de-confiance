<?php

use App\Models\User;

test('debug dump', function () {
    $parent = User::factory()->parent()->create();

    $response = $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->get(route('home'));

    file_put_contents(sys_get_temp_dir().'/debug-home.html', $response->getContent());

    expect(true)->toBeTrue();
});
