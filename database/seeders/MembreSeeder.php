<?php

namespace Database\Seeders;

use App\Models\Membre;
use Illuminate\Database\Seeder;

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

        $users = json_decode(file_get_contents($path), true);

        foreach ($users as $userData) {
            Membre::updateOrCreate(
                ['courriel' => $userData['courriel']],
                [
                    'nom'      => $userData['nom'],
                    'titre'    => $userData['titre'],
                    'role'     => $userData['role'] ?? '',
                    'photo'    => $userData['photo'] ?? null,
                    'courriel' => $userData['courriel'] ?? null,
                ]
            );
        }
    }
}
