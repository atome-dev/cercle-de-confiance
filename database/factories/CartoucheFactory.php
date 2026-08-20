<?php

namespace Database\Factories;

use App\Models\Cartouche;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cartouche>
 */
class CartoucheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icone' => $this->faker->randomElement(['🤝', '⚖️', '💬', '🌱', '📌']),
            'titre' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
        ];
    }
}
