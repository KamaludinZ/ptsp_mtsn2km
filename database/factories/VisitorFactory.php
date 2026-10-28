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
            'phone' => $this->faker->numerify('08##########'),
            'institution' => $this->faker->company(),
            'purpose' => $this->faker->sentence(),
            // From the app's clock, so tests that travel in time stay consistent.
            'check_in_time' => $this->faker->dateTimeBetween(now()->subMonth(), now()),
            // Left within three hours of arriving, or still on site; never in the future.
            'check_out_time' => fn (array $attributes) => $this->faker->boolean()
                ? min(\Illuminate\Support\Carbon::parse($attributes['check_in_time'])->addMinutes($this->faker->numberBetween(15, 180)), now())
                : null,
            'is_obscured' => $this->faker->boolean(),
            'person_to_meet' => $this->faker->name(),
            'created_by' => User::all()->random()->id,
        ];
    }
}
