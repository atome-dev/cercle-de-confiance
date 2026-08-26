<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('seeders.admin_password');
        ray($password);
        if (! empty($password)) {
            $user = User::updateOrCreate(
                ['email' => 'admin@localhost'],
                [
                    'name' => 'Administrateur',
                    'password' => Hash::make($password),
                ]
            );

            $user->assignRole('administrateur', 'membre');
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
