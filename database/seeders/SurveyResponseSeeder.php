<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class SurveyResponseSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a survey
        $survey = Survey::first();
        if (!$survey) {
            $survey = Survey::create([
                'title' => 'Survey Kepuasan Masyarakat 2025',
                'description' => 'Survey kepuasan layanan PTSP MTsN 2 Kota Malang',
                'is_active' => true,
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->addMonths(6),
            ]);
        }

        // Get survey questions
        $questions = SurveyQuestion::all();
        $identityQuestions = SurveyQuestion::where('survey_type', 'identity')->get();
        $skmQuestions = SurveyQuestion::where('survey_type', 'skm')->get();

        if ($identityQuestions->isEmpty() || $skmQuestions->isEmpty()) {
            $this->command->error('Survey questions not found. Please run SurveyQuestionSeeder first.');
            return;
        }

        // Get users and tickets
        $users = User::all();
        $tickets = Ticket::all();

        // Demographic options
        $ageOptions = ['Dibawah 20 Tahun', '21 s.d 30 Tahun', '31 s.d 40 Tahun', '41 s.d 50 Tahun', 'Diatas 50 Tahun'];
        $educationOptions = ['SD', 'SMP', 'SMA', 'D3', 'D4/S1', 'S2', 'S3'];
        $occupationOptions = ['PNS/TNI/POLRI', 'Pegawai Swasta', 'Wiraswasta', 'Petani/Pekebun', 'Pelajar/Mahasiswa', 'Lainnya'];
        $serviceOptions = [
            'Mutasi Siswa Masuk',
            'Mutasi Siswa Keluar',
            'Penerbitan Surat Rekomendasi Siswa',
            'Penerimaan Peserta Didik Baru',
            'Izin Melaksanakan Penelitian/Observasi',
            'Legalisasi Ijazah Offline',
            'Pengambilan Ijazah',
        ];

        // Rating options (1-4 for SKM)
        $ratings = [1, 2, 3, 4];

        // Generate 100 responses over the last 12 months
        $this->command->info('Generating 100 survey responses...');

        for ($i = 0; $i < 100; $i++) {
            // Random date in the last 12 months
            $createdAt = Carbon::now()->subDays(rand(0, 365));

            // Create survey response
            $response = SurveyResponse::create([
                'survey_id' => $survey->id,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'ticket_id' => $tickets->isNotEmpty() ? $tickets->random()->id : null,
                'respondent_email' => 'respondent' . $i . '@example.com',
                'ip_address' => '192.168.1.' . rand(1, 255),
                'completed_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Answer identity questions
            foreach ($identityQuestions as $question) {
                $answer = null;

                if (str_contains($question->question, 'Usia')) {
                    $answer = $ageOptions[array_rand($ageOptions)];
                } elseif (str_contains($question->question, 'Pendidikan')) {
                    $answer = $educationOptions[array_rand($educationOptions)];
                } elseif (str_contains($question->question, 'Pekerjaan')) {
                    $answer = $occupationOptions[array_rand($occupationOptions)];
                } elseif (str_contains($question->question, 'Jenis Pelayanan')) {
                    $answer = $serviceOptions[array_rand($serviceOptions)];
                } elseif (str_contains($question->question, 'Nama Lengkap')) {
                    $answer = 'Responden ' . $i;
                } elseif (str_contains($question->question, 'Jenis Kelamin')) {
                    $answer = rand(0, 1) ? 'Laki-laki' : 'Perempuan';
                } elseif (str_contains($question->question, 'No. Telepon')) {
                    $answer = '08' . rand(1000000000, 9999999999);
                } elseif (str_contains($question->question, 'Kode Tiket')) {
                    $answer = 'TKT-' . str_pad($i, 6, '0', STR_PAD_LEFT);
                } elseif (str_contains($question->question, 'Email')) {
                    $answer = 'respondent' . $i . '@example.com';
                }

                if ($answer) {
                    SurveyAnswer::create([
                        'survey_response_id' => $response->id,
                        'survey_question_id' => $question->id,
                        'answer_text' => $answer,
                        'selected_option' => $answer,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }

            // Answer SKM questions with ratings (mostly positive - rating 3-4)
            foreach ($skmQuestions as $question) {
                // 70% chance of rating 3-4 (good/very good)
                $rating = rand(1, 100) <= 70 ? $ratings[rand(2, 3)] : $ratings[rand(0, 1)];

                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $question->id,
                    'rating_value' => $rating,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('Survey responses seeded successfully! Created 100 responses with answers.');
    }
}
