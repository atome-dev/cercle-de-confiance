<?php

namespace Database\Factories;

use App\Enums\AvailabilityMode;
use App\Models\MeetingAvailability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingAvailability>
 */
class MeetingAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->parent(),
            'starts_at' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d').' '.$this->faker->randomElement(MeetingAvailability::slotTimes()),
            'mode' => $this->faker->randomElement(AvailabilityMode::cases()),
        ];
    }

    public function inPerson(): static
    {
        return $this->state(['mode' => AvailabilityMode::InPerson]);
    }

    public function remote(): static
    {
        return $this->state(['mode' => AvailabilityMode::Remote]);
    }
}
