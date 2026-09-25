<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'LAYANAN-' . date('Ym') . '-' . str_pad(fake()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'mode' => fake()->randomElement(['online', 'offline']),
            'status' => fake()->randomElement(['submitted', 'verified', 'in_process', 'approved', 'rejected', 'completed', 'cancelled']),
            'created_by' => fn (array $attributes) => $attributes['user_id'],
        ];
    }
}