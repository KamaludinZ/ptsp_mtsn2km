<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyEdition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default SKM & SPAK Survey
        Survey::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Survey Kepuasan Masyarakat & Persepsi Anti Korupsi ' . now()->year,
                'description' => 'Survey tahunan untuk mengukur Indeks Kepuasan Masyarakat (IKM) dan Indeks Persepsi Anti Korupsi (IPAK) di MTsN 2 Kota Malang sesuai Permenpan RB No. 14 Tahun 2017',
                'type' => 'other', // Combined SKM + SPAK
                'is_active' => true,
                'start_date' => Carbon::create(now()->year, 1, 1),
                'end_date' => Carbon::create(now()->year, 12, 31),
            ]
        );

        $this->command->info('✅ Default Survey created successfully!');
        $this->command->info('   Survey ID: 1');
        $this->command->info('   Name: Survey Kepuasan Masyarakat & Persepsi Anti Korupsi ' . now()->year);
        $this->command->info('   Type: Other (Combined SKM + SPAK)');
        $this->command->info('   Period: ' . now()->year);

        // Create an active SurveyEdition for the current quarter
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $quarter = '';
        $start_date = null;
        $end_date = null;

        if ($currentMonth >= 1 && $currentMonth <= 3) {
            $quarter = 'Q1';
            $start_date = Carbon::create($currentYear, 1, 1)->startOfDay();
            $end_date = Carbon::create($currentYear, 3, 31)->endOfDay();
        } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
            $quarter = 'Q2';
            $start_date = Carbon::create($currentYear, 4, 1)->startOfDay();
            $end_date = Carbon::create($currentYear, 6, 30)->endOfDay();
        } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
            $quarter = 'Q3';
            $start_date = Carbon::create($currentYear, 7, 1)->startOfDay();
            $end_date = Carbon::create($currentYear, 9, 30)->endOfDay();
        } else {
            $quarter = 'Q4';
            $start_date = Carbon::create($currentYear, 10, 1)->startOfDay();
            $end_date = Carbon::create($currentYear, 12, 31)->endOfDay();
        }

        SurveyEdition::updateOrCreate(
            ['year' => $currentYear, 'period' => $quarter],
            [
                'name' => "Edisi Survei Triwulan " . substr($quarter, 1) . " " . $currentYear,
                'type' => 'quarterly',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Default Survey Edition created successfully!');
        $this->command->info('   Edition Name: Edisi Survei Triwulan ' . substr($quarter, 1) . ' ' . $currentYear);
        $this->command->info('   Period: ' . $quarter);
    }
}
