<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_type' => fake()->randomElement(['complaint', 'suggestion', 'whistleblowing']),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'complainant_name' => fake()->name(),
            'complainant_email' => fake()->safeEmail(),
            'status' => fake()->randomElement(['submitted', 'in_review', 'in_progress', 'resolved', 'closed']),
            'anonymous' => fake()->boolean(),
        ];
    }
}