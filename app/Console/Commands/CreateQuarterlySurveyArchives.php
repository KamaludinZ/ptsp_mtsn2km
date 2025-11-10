<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SurveyArchive;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use Carbon\Carbon;
use DB;

class CreateQuarterlySurveyArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-quarterly-survey-archives 
                            {--type= : Survey type to archive (spak, skm)} 
                            {--year= : Year to archive (defaults to current year)}
                            {--quarter= : Quarter to archive (Q1, Q2, Q3, Q4, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create quarterly archives for SPAK/SKM survey results according to Permenpan No. 14 2017';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating quarterly survey archives...');
        
        $surveyType = $this->option('type') ?? 'spak';
        $year = $this->option('year') ?? now()->year;
        $quarter = $this->option('quarter') ?? 'all';

        if ($quarter === 'all') {
            $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        } else {
            $quarters = [$quarter];
        }

        foreach ($quarters as $q) {
            $this->createQuarterlyArchive($surveyType, $year, $q);
        }

        $this->info('Quarterly survey archives created successfully!');
    }

    protected function createQuarterlyArchive($surveyType, $year, $quarter)
    {
        $startDate = null;
        $endDate = null;
        
        switch ($quarter) {
            case 'Q1':
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 3, 31);
                break;
            case 'Q2':
                $startDate = Carbon::create($year, 4, 1);
                $endDate = Carbon::create($year, 6, 30);
                break;
            case 'Q3':
                $startDate = Carbon::create($year, 7, 1);
                $endDate = Carbon::create($year, 9, 30);
                break;
            case 'Q4':
                $startDate = Carbon::create($year, 10, 1);
                $endDate = Carbon::create($year, 12, 31);
                break;
        }

        if (!$startDate || !$endDate) {
            $this->error("Invalid quarter: {$quarter}");
            return;
        }

        // Check if archive already exists
        $existingArchive = SurveyArchive::where('type', $surveyType)
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->first();

        if ($existingArchive) {
            $this->line("Archive for {$surveyType} {$quarter} {$year} already exists.");
            return;
        }

        // Get SPAK or SKM questions
        $questions = SurveyQuestion::where('survey_type', $surveyType)
            ->with('unsur')
            ->get();

        // Get responses for the specified quarter
        $responses = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', $surveyType)
            ->whereBetween('sr.created_at', [$startDate, $endDate])
            ->select('sr.id as response_id', 'sr.created_at', 'sq.id as question_id', 'sq.question', 'sq.type', 'sq.category', 'sa.answer_text', 'sa.rating_value', 'sa.selected_option')
            ->get();

        if ($responses->isEmpty()) {
            $this->warn("No responses found for {$surveyType} {$quarter} {$year}, skipping...");
            return;
        }

        // Calculate results using Permenpan No. 14 Year 2017 methodology
        $calculatedResults = $this->calculateSurveyResultsPermenpan142017($responses, $questions);

        // Save the archive
        $archive = SurveyArchive::create([
            'type' => $surveyType,
            'quarter' => $quarter,
            'year' => $year,
            'period' => null, // Not a monthly period
            'data' => [
                'responses_count' => $responses->pluck('response_id')->unique()->count(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'calculation_method' => 'Permenpan No. 14 2017',
                'raw_data' => $responses->toArray()
            ],
            'calculated_values' => $calculatedResults,
            'is_quarterly_archive' => true
        ]);

        $this->info("Created archive for {$surveyType} {$quarter} {$year}: Respondents={$calculatedResults['total_respondents']}, Average={$calculatedResults['average']}, Percentage={$calculatedResults['percentage']}%");
    }

    protected function calculateSurveyResultsPermenpan142017($responses, $questions)
    {
        // Permenpan RB No. 14 Year 2017 methodology for calculating survey results
        $results = [
            'total_respondents' => $responses->pluck('response_id')->unique()->count(),
            'scores' => [],
            'average' => 0,
            'percentage' => 0
        ];

        // Group responses by question ID
        $responsesByQuestion = $responses->groupBy('question_id');
        
        $totalScore = 0;
        $totalQuestions = 0;

        foreach ($questions as $question) {
            $questionResponses = $responsesByQuestion->get($question->id, collect());

            if ($questionResponses->isEmpty()) {
                continue;
            }

            // Calculate average score for each question
            $questionScores = $questionResponses->pluck('rating_value')
                ->filter(fn($score) => !is_null($score))
                ->values();

            if ($questionScores->isNotEmpty()) {
                $avgScore = $questionScores->avg() ?? 0;
                
                $results['scores'][] = [
                    'question' => $question->question,
                    'average_score' => round($avgScore, 2),
                    'total_responses' => $questionScores->count()
                ];
                
                $totalScore += $avgScore;
                $totalQuestions++;
            }
        }

        if ($totalQuestions > 0) {
            $results['average'] = round($totalScore / $totalQuestions, 2);
            
            // Convert to percentage based on 4-scale (minimum 1, maximum 4) according to Permenpan RB No. 14/2017
            // Formula: ((average - 1) / (4 - 1)) * 100
            $results['percentage'] = round((($results['average'] - 1) / 3) * 100, 2);
        } else {
            $results['average'] = 0;
            $results['percentage'] = 0;
        }

        return $results;
    }
}
