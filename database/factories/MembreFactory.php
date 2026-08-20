<?php

namespace Database\Factories;

use App\Models\Membre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membre>
 */
class MembreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->name(),
            'titre' => $this->faker->randomElement(["Parent d'élève", 'Professeur', 'Salarié']),
            'role' => $this->faker->randomElement(['parent', 'professeur', 'salarie']),
            'photo' => null,
            'courriel' => $this->faker->safeEmail(),
        ];
    }
}
