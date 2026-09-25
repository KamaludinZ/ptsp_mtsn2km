<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Ticket::factory()
            ->count(20)
            // Attach tickets to the seeded services/users instead of creating fake ones
            ->recycle(\App\Models\Service::all())
            ->recycle(\App\Models\User::all())
            ->create();
    }
}