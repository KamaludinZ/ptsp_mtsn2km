<?php

namespace App\Support;

use App\Models\SurveyArchive;
use App\Models\SurveyQuestion;
use Carbon\CarbonInterface;

/**
 * SKM (IKM) and SPAK (IPAK) reports for a period (the current month unless
 * given) and optionally one survey edition, following the Permenpan RB
 * 14/2017 methodology, plus the respondent demographics.
 */
class SurveyReports
{
    private CarbonInterface $from;

    private CarbonInterface $to;

    public function __construct(?CarbonInterface $from = null, ?CarbonInterface $to = null, private ?int $editionId = null)
    {
        $this->from = $from ?? now()->startOfMonth();
        $this->to = $to ?? now()->endOfMonth();
    }

    public function skm(): array
    {
        return $this->getCurrentMonthSkmData();
    }

    public function spak(): array
    {
        return $this->getCurrentMonthSpakData();
    }

    /** Quarterly archives of one survey type, newest year first. */
    public function archives(string $type)
    {
        return SurveyArchive::where('type', $type)
            ->where('is_quarterly_archive', true)
            ->orderBy('year', 'desc')
            ->orderByRaw("CASE quarter WHEN 'Q1' THEN 1 WHEN 'Q2' THEN 2 WHEN 'Q3' THEN 3 WHEN 'Q4' THEN 4 END")
            ->get();
    }

    public function getCurrentMonthSkmData()
    {
        // Get SKM questions
        $skmQuestions = SurveyQuestion::where('survey_type', 'skm')
            ->with('unsur')
            ->get();

        // Get current month's responses
        $currentResponses = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', 'skm')
            ->whereBetween('sr.created_at', [$this->from, $this->to])
            ->when($this->editionId, fn ($q) => $q->where('sr.survey_edition_id', $this->editionId))
            ->select('sr.id as response_id', 'sr.created_at', 'sq.id as question_id', 'sq.question', 'sq.type', 'sq.category', 'sa.answer_text', 'sa.rating_value', 'sa.selected_option')
            ->get();

        // Calculate current month's results using Permenpan No. 14 2017 methodology
        $calculatedResults = $this->calculateSkmResultsPermenpan142017($currentResponses, $skmQuestions);

        // Aggregate demographic data for current month
        $demographics = $this->getSkmDemographicDataForCurrentMonth();

        return [
            'results' => $calculatedResults,
            'demographics' => $demographics,
            'responses_count' => $currentResponses->count()
        ];
    }

    public function calculateSkmResultsPermenpan142017($responses, $questions)
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
            // Range: (average - 1) / (4 - 1) * 100 = (average - 1) / 3 * 100
            $results['percentage'] = round((($results['average'] - 1) / 3) * 100, 2);
        } else {
            $results['average'] = 0;
            $results['percentage'] = 0;
        }

        return $results;
    }

    public function getSkmDemographicDataForCurrentMonth()
    {
        // Get demographic info from identity questions in survey responses
        $identityAnswers = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', 'identity')
            ->whereBetween('sr.created_at', [$this->from, $this->to])
            ->when($this->editionId, fn ($q) => $q->where('sr.survey_edition_id', $this->editionId))
            ->select('sq.question', 'sa.answer_text', 'sa.selected_option')
            ->get();

        // Group by demographic categories (age, education, job, service_type) - adjusted for SKM
        $ageGroups = [];
        $educationLevels = [];
        $jobTypes = [];
        $serviceTypes = [];
        
        foreach ($identityAnswers as $answer) {
            $questionLower = strtolower($answer->question);
            
            if (str_contains($questionLower, 'umur') || str_contains($questionLower, 'age') || str_contains($questionLower, 'usia')) {
                $ageValue = $answer->answer_text ?? $answer->selected_option;
                if ($ageValue) {
                    $ageGroups[$ageValue] = ($ageGroups[$ageValue] ?? 0) + 1;
                }
            } elseif (str_contains($questionLower, 'pendidikan') || str_contains($questionLower, 'education')) {
                $educationValue = $answer->answer_text ?? $answer->selected_option;
                if ($educationValue) {
                    $educationLevels[$educationValue] = ($educationLevels[$educationValue] ?? 0) + 1;
                }
            } elseif (str_contains($questionLower, 'pekerjaan') || str_contains($questionLower, 'job') || str_contains($questionLower, 'profesi')) {
                $jobValue = $answer->answer_text ?? $answer->selected_option;
                if ($jobValue) {
                    $jobTypes[$jobValue] = ($jobTypes[$jobValue] ?? 0) + 1;
                }
            } elseif (str_contains($questionLower, 'layan') || str_contains($questionLower, 'jenis')) {
                $serviceValue = $answer->answer_text ?? $answer->selected_option;
                if ($serviceValue) {
                    $serviceTypes[$serviceValue] = ($serviceTypes[$serviceValue] ?? 0) + 1;
                }
            }
        }

        return [
            'age_groups' => $ageGroups,
            'education_levels' => $educationLevels,
            'job_types' => $jobTypes,
            'service_types' => $serviceTypes
        ];
    }

    public function getCurrentMonthSpakData()
    {
        // Get SPAK questions
        $spakQuestions = SurveyQuestion::where('survey_type', 'spak')
            ->with('unsur')
            ->get();

        // Get current month's responses
        $currentResponses = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', 'spak')
            ->whereBetween('sr.created_at', [$this->from, $this->to])
            ->when($this->editionId, fn ($q) => $q->where('sr.survey_edition_id', $this->editionId))
            ->select('sr.id as response_id', 'sr.created_at', 'sq.id as question_id', 'sq.question', 'sq.type', 'sq.category', 'sa.answer_text', 'sa.rating_value', 'sa.selected_option')
            ->get();

        // Calculate current month's results using Permenpan No. 14 2017 methodology
        $calculatedResults = $this->calculateSpakResultsPermenpan142017($currentResponses, $spakQuestions);

        // Aggregate demographic data for current month
        $demographics = $this->getDemographicDataForCurrentMonth();

        return [
            'results' => $calculatedResults,
            'demographics' => $demographics,
            'responses_count' => $currentResponses->count()
        ];
    }

    public function calculateSpakResultsPermenpan142017($responses, $questions)
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
            // Range: (average - 1) / (4 - 1) * 100 = (average - 1) / 3 * 100
            $results['percentage'] = round((($results['average'] - 1) / 3) * 100, 2);
        } else {
            $results['average'] = 0;
            $results['percentage'] = 0;
        }

        return $results;
    }

    public function getDemographicDataForCurrentMonth()
    {
        // Assuming demographics come from identity questions in survey responses
        $identityAnswers = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', 'identity')
            ->whereBetween('sr.created_at', [$this->from, $this->to])
            ->when($this->editionId, fn ($q) => $q->where('sr.survey_edition_id', $this->editionId))
            ->select('sq.question', 'sa.answer_text', 'sa.selected_option')
            ->get();

        // Group by demographic categories (age, education, job, service_type)
        $ageGroups = [];
        $educationLevels = [];
        $jobTypes = [];
        $serviceTypes = [];
        
        foreach ($identityAnswers as $answer) {
            $questionLower = strtolower($answer->question);
            
            if (str_contains($questionLower, 'umur') || str_contains($questionLower, 'age') || str_contains($questionLower, 'usia')) {
                $ageGroups[$answer->answer_text ?? $answer->selected_option] = 
                    ($ageGroups[$answer->answer_text ?? $answer->selected_option] ?? 0) + 1;
            } elseif (str_contains($questionLower, 'pendidikan') || str_contains($questionLower, 'education')) {
                $educationLevels[$answer->answer_text ?? $answer->selected_option] = 
                    ($educationLevels[$answer->answer_text ?? $answer->selected_option] ?? 0) + 1;
            } elseif (str_contains($questionLower, 'pekerjaan') || str_contains($questionLower, 'job') || str_contains($questionLower, 'profesi')) {
                $jobTypes[$answer->answer_text ?? $answer->selected_option] = 
                    ($jobTypes[$answer->answer_text ?? $answer->selected_option] ?? 0) + 1;
            } elseif (str_contains($questionLower, 'layanan') || str_contains($questionLower, 'jenis layanan')) {
                $serviceTypes[$answer->answer_text ?? $answer->selected_option] = 
                    ($serviceTypes[$answer->answer_text ?? $answer->selected_option] ?? 0) + 1;
            }
        }

        return [
            'age_groups' => $ageGroups,
            'education_levels' => $educationLevels,
            'job_types' => $jobTypes,
            'service_types' => $serviceTypes
        ];
    }
}
