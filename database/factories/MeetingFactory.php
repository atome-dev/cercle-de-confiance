<?php

namespace Database\Factories;

use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'held_on' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'starts_at' => $this->faker->randomElement([null, '18:00', '20:30']),
            'title' => $this->faker->sentence(3),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
