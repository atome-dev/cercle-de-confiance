<?php

use App\Models\User;
use Database\Seeders\MembreSeeder;
use Database\Seeders\RoleSeeder;

function withTemporaryUsersJson(array $users, Closure $callback): void
{
    $path = storage_path('app/private/users.json');
    $existed = file_exists($path);
    $backup = $existed ? file_get_contents($path) : null;

    file_put_contents($path, json_encode($users));

    try {
        $callback();
    } finally {
        if ($existed) {
            file_put_contents($path, $backup);
        } else {
            unlink($path);
        }
    }
}

test('the seeder persists membre_titre, membre_role, photo and email_verified_at on the user', function () {
    withTemporaryUsersJson([
        [
            'nom' => 'Nicolas Chauvet',
            'titre' => "Parent d'élève",
            'role' => 'parent',
            'courriel' => 'nicolas.chauvet@example.com',
            'photo' => 'nicolas-chauvet.jpg',
        ],
    ], function () {
        $this->seed(RoleSeeder::class);
        $this->seed(MembreSeeder::class);

        $user = User::where('email', 'nicolas.chauvet@example.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->name)->toBe('Nicolas Chauvet')
            ->and($user->membre_titre)->toBe("Parent d'élève")
            ->and($user->membre_role)->toBe('parent')
            ->and($user->photo)->toBe('nicolas-chauvet.jpg')
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->hasRole('parent'))->toBeTrue();
    });
});

test('the seeder updates an existing user found by email instead of duplicating it', function () {
    withTemporaryUsersJson([
        [
            'nom' => 'Nouveau Nom',
            'titre' => 'Professeur de mathématiques',
            'role' => 'professeur',
            'courriel' => 'existant@example.com',
        ],
    ], function () {
        $existing = User::factory()->create(['email' => 'existant@example.com', 'name' => 'Ancien Nom']);

        $this->seed(RoleSeeder::class);
        $this->seed(MembreSeeder::class);

        expect(User::where('email', 'existant@example.com')->count())->toBe(1)
            ->and($existing->fresh()->name)->toBe('Nouveau Nom')
            ->and($existing->fresh()->membre_titre)->toBe('Professeur de mathématiques');
    });
});
