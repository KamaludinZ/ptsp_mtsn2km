<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceSeeder::class,
            SchoolServicesSeeder::class,
            SurveySeeder::class,              // Create default survey
            SurveyUnsurSeeder::class,         // Create survey unsurs
            SurveyQuestionSeeder::class,      // Create survey questions
            FaqSeeder::class,
        ]);

        // Fake tickets, visitors, complaints and announcements are demo data
        // only; never publish them on the live site.
        if (!app()->isProduction()) {
            $this->call([
                TicketSeeder::class,
                VisitorSeeder::class,
                ComplaintSeeder::class,
                PengumumanSeeder::class,
            ]);
        }
    }
}