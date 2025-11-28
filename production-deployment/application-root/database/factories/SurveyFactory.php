<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['skm', 'spak', 'other']),
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ];
    }
}