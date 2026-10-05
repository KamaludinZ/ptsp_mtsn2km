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
            RolePermissionSeeder::class,      // system roles and their default permissions
            UserSeeder::class,
            ServiceSeeder::class,
            SchoolServicesSeeder::class,
            ServiceCatalogSeeder::class,     // categories, disposition rules, requirement rows
            SurveySeeder::class,              // Create default survey
            SurveyUnsurSeeder::class,         // Create survey unsurs
            SurveyQuestionSeeder::class,      // Create survey questions
            FaqSeeder::class,
            PersuratanMasterSeeder::class,
            NotificationTemplateSeeder::class,
        ]);

        // Fake tickets, visitors, complaints and announcements are demo data
        // only; never publish them on the live site.
        if (!app()->isProduction()) {
            // Well-known demo civitas registration code
            \App\Support\CivitasRegistration::setCode('1234567890');

            $this->call([
                TicketSeeder::class,
                VisitorSeeder::class,
                ComplaintSeeder::class,
                PengumumanSeeder::class,
                SuratKeluarSeeder::class,
                NotificationSeeder::class,
            ]);
        }
    }
}