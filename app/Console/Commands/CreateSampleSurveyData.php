<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SurveyQuestion;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Models\SurveyUnsur;
use App\Models\User;
use App\Models\Ticket;

class CreateSampleSurveyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-sample-survey-data {--reset : Reset all existing survey data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample SPAK/SKM survey questions and responses for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('reset')) {
            $this->info('Removing existing survey data...');
            SurveyAnswer::truncate();
            SurveyResponse::truncate();
            SurveyQuestion::truncate();
            Survey::truncate();
        }

        $this->info('Creating sample survey data...');

        // Create sample SPAK questions
        $spakQuestions = [
            ['question' => 'Apakah petugas melayani dengan ramah dan profesional?', 'type' => 'rating', 'survey_type' => 'spak', 'field_type' => 'rating'],
            ['question' => 'Apakah terdapat kecurangan atau praktik korupsi dalam proses pelayanan?', 'type' => 'rating', 'survey_type' => 'spak', 'field_type' => 'rating'],
            ['question' => 'Apakah petugas tidak meminta imbalan tambahan diluar ketentuan?', 'type' => 'rating', 'survey_type' => 'spak', 'field_type' => 'rating'],
            ['question' => 'Apakah prosedur pelayanan sudah jelas dan transparan?', 'type' => 'rating', 'survey_type' => 'spak', 'field_type' => 'rating'],
            ['question' => 'Apakah waktu pelayanan sesuai dengan yang ditentukan?', 'type' => 'rating', 'survey_type' => 'spak', 'field_type' => 'rating'],
        ];

        foreach ($spakQuestions as $question) {
            SurveyQuestion::create([
                'question' => $question['question'],
                'type' => $question['type'],
                'survey_type' => $question['survey_type'],
                'field_type' => $question['field_type'],
                'is_required' => true,
                'is_active' => true,
                'order' => 1
            ]);
        }

        // Create sample identity questions
        $identityQuestions = [
            ['question' => 'Usia Anda', 'type' => 'text', 'survey_type' => 'identity', 'field_type' => 'text'],
            ['question' => 'Jenis Kelamin', 'type' => 'select', 'survey_type' => 'identity', 'field_type' => 'select'],
            ['question' => 'Pendidikan Terakhir', 'type' => 'select', 'survey_type' => 'identity', 'field_type' => 'select'],
            ['question' => 'Pekerjaan', 'type' => 'text', 'survey_type' => 'identity', 'field_type' => 'text'],
        ];

        foreach ($identityQuestions as $question) {
            SurveyQuestion::create([
                'question' => $question['question'],
                'type' => $question['type'],
                'survey_type' => $question['survey_type'],
                'field_type' => $question['field_type'],
                'is_required' => true,
                'is_active' => true,
                'order' => 1
            ]);
        }

        // Get or create a user and ticket for the responses
        $user = User::first();
        $ticket = Ticket::first();

        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]);
        }
        
        if (!$ticket) {
            $ticket = Ticket::create([
                'ticket_number' => 'TKT-' . now()->timestamp,
                'service_id' => 1,
                'applicant_name' => 'Test Applicant',
                'applicant_email' => 'applicant@example.com',
                'applicant_phone' => '+6281234567890',
                'status' => 'open'
            ]);
        }

        // Create sample responses
        for ($i = 0; $i < 50; $i++) {
            $response = SurveyResponse::create([
                'survey_id' => 1, // Assuming there's a survey
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'ip_address' => '127.0.0.1',
                'completed_at' => now()->subDays(rand(0, 30))
            ]);

            // Add SPAK answers (with random ratings between 1 and 4)
            $spakQuestions = SurveyQuestion::where('survey_type', 'spak')->get();
            foreach ($spakQuestions as $question) {
                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $question->id,
                    'answer_text' => null,
                    'rating_value' => rand(1, 4),
                    'selected_option' => null
                ]);
            }

            // Add Identity answers
            $identityQuestions = SurveyQuestion::where('survey_type', 'identity')->get();
            foreach ($identityQuestions as $question) {
                $answers = [
                    'Usia Anda' => ['18-25', '26-35', '36-45', '46-55', '56+'],
                    'Jenis Kelamin' => ['Laki-laki', 'Perempuan'],
                    'Pendidikan Terakhir' => ['SD', 'SMP', 'SMA', 'Diploma', 'Sarjana', 'Magister', 'Doktor', 'Lainnya'],
                    'Pekerjaan' => ['PNS', 'Swasta', 'Wiraswasta', 'Pelajar/Mahasiswa', 'Ibu Rumah Tangga', 'Lainnya']
                ];
                
                $answerText = null;
                $selectedOption = null;
                
                if ($question->question == 'Usia Anda') {
                    $answerText = $answers['Usia Anda'][array_rand($answers['Usia Anda'])];
                } elseif ($question->question == 'Jenis Kelamin') {
                    $selectedOption = $answers['Jenis Kelamin'][array_rand($answers['Jenis Kelamin'])];
                } elseif ($question->question == 'Pendidikan Terakhir') {
                    $selectedOption = $answers['Pendidikan Terakhir'][array_rand($answers['Pendidikan Terakhir'])];
                } elseif ($question->question == 'Pekerjaan') {
                    $answerText = $answers['Pekerjaan'][array_rand($answers['Pekerjaan'])];
                }
                
                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $question->id,
                    'answer_text' => $answerText,
                    'rating_value' => null,
                    'selected_option' => $selectedOption
                ]);
            }
        }

        $this->info('Sample survey data created successfully!');
    }
}
