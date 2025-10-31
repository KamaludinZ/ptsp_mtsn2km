<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
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
            'code' => fake()->unique()->bothify('SRV-#####'),
            'description' => fake()->paragraph(),
            'user_types_allowed' => ['guru', 'pegawai', 'siswa', 'walimurid', 'alumni', 'instansi', 'umum'],
            'is_active' => true,
            'created_by' => 1, // Assuming user ID 1 exists
        ];
    }
}