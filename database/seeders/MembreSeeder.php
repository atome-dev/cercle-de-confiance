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

        $hashedPassword = Hash::make(config('seeders.default_password') ?: str()->random(32));
        $users = json_decode(file_get_contents($path), true);

        foreach ($users as $userData) {
            // User::$fillable only allows mass-assigning name/email/password,
            // so membre_titre/membre_role/photo/email_verified_at are set
            // individually — updateOrCreate() would silently drop them.
            $user = User::firstOrNew(['email' => $userData['courriel']]);

            $user->name = $userData['nom'];
            $user->membre_titre = $userData['titre'];
            $user->membre_role = $userData['role'] ?? '';
            $user->photo = $userData['photo'] ?? null;
            $user->email_verified_at = now();
            $user->password = $hashedPassword;
            $user->save();

            if (in_array($userData['role'] ?? null, ['parent', 'professeur'], true)) {
                $user->syncRoles([$userData['role']]);
            }
        }
    }
}
