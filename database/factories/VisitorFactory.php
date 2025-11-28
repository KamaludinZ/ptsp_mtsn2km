<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'institution' => $this->faker->company(),
            'purpose' => $this->faker->sentence(),
            'check_in_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'check_out_time' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'is_obscured' => $this->faker->boolean(),
            'person_to_meet' => $this->faker->name(),
            'created_by' => User::all()->random()->id,
        ];
    }
}
