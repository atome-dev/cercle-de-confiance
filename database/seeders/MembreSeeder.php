<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MembreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/private/users.json');

        if (! file_exists($path)) {
            $this->command->warn("Fichier de données introuvable : $path");

            return;
        }

        $hashedPassword = Hash::make(config('seeders.admin_password') ?: str()->random(32));
        $users = json_decode(file_get_contents($path), true);

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['courriel']],
                [
                    'name' => $userData['nom'],
                    'membre_titre' => $userData['titre'],
                    'membre_role' => $userData['role'] ?? '',
                    'photo' => $userData['photo'] ?? null,
                    'email_verified_at' => now(),
                    'password' => $hashedPassword,
                ]
            );

            if (in_array($userData['role'] ?? null, ['parent', 'professeur'], true)) {
                $user->syncRoles([$userData['role']]);
            }
        }
    }
}
