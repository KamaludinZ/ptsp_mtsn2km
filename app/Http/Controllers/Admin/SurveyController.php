<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyEdition;
use App\Models\SurveyUnsur;
use App\Models\SurveyArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function management()
    {
        $identityQuestions = SurveyQuestion::where('survey_type', 'identity')->where('is_active', true)->orderBy('order')->get();
        $skmQuestions = SurveyQuestion::where('survey_type', 'skm')->where('is_active', true)->with('unsur')->orderBy('order')->get();
        $spakQuestions = SurveyQuestion::where('survey_type', 'spak')->where('is_active', true)->with('unsur')->orderBy('order')->get();
        
        \Log::info('Identity Questions:', $identityQuestions->toArray());
        \Log::info('SKM Questions:', $skmQuestions->toArray());
        \Log::info('SPAK Questions:', $spakQuestions->toArray());

        $activeEdition = SurveyEdition::where('is_active', true)
                                    ->where('start_date', '<', now())
                                    ->where('end_date', '>', now())
                                    ->first();

        $skmUnsurs = SurveyUnsur::where('survey_type', 'skm')->get();
        $spakUnsurs = SurveyUnsur::where('survey_type', 'spak')->get();

        return view('admin.survey.management', compact('identityQuestions', 'skmQuestions', 'spakQuestions', 'activeEdition', 'skmUnsurs', 'spakUnsurs'));
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
            $results['percentage'] = round((($results['average'] - 1) / 3) * 100, 2);
        } else {
            $results['average'] = 0;
            $results['percentage'] = 0;
        }

        return $results;
    }

    public function skmReport()
    {
        // Get current month's survey data for demographic information
        $currentMonthData = $this->getCurrentMonthSkmData();
        
        // Get quarterly archives
        $quarterlyArchives = SurveyArchive::where('type', 'skm')
            ->where('is_quarterly_archive', true)
            ->orderBy('year', 'desc')
            ->orderByRaw("CASE quarter 
                WHEN 'Q1' THEN 1 
                WHEN 'Q2' THEN 2 
                WHEN 'Q3' THEN 3 
                WHEN 'Q4' THEN 4 
            END")
            ->get();

        return view('admin.survey.skm-report', compact('currentMonthData', 'quarterlyArchives'));
    }
    
    protected function getCurrentMonthSkmData()
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
            ->whereMonth('sr.created_at', now()->month)
            ->whereYear('sr.created_at', now()->year)
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

    protected function calculateSkmResultsPermenpan142017($responses, $questions)
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

    protected function getSkmDemographicDataForCurrentMonth()
    {
        // Get demographic info from identity questions in survey responses
        $identityAnswers = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', 'identity')
            ->whereMonth('sr.created_at', now()->month)
            ->whereYear('sr.created_at', now()->year)
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

    public function spakReport()
    {
        // Get current month's survey data for demographic information
        $currentMonthData = $this->getCurrentMonthSpakData();
        
        // Get quarterly archives
        $quarterlyArchives = SurveyArchive::where('type', 'spak')
            ->where('is_quarterly_archive', true)
            ->orderBy('year', 'desc')
            ->orderByRaw("CASE quarter 
                WHEN 'Q1' THEN 1 
                WHEN 'Q2' THEN 2 
                WHEN 'Q3' THEN 3 
                WHEN 'Q4' THEN 4 
            END")
            ->get();

        return view('admin.survey.spak-report', compact('currentMonthData', 'quarterlyArchives'));
    }
    
    public function getArchiveDetail($id)
    {
        $archive = SurveyArchive::findOrFail($id);
        return response()->json($archive);
    }

    protected function getCurrentMonthSpakData()
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
            ->whereMonth('sr.created_at', now()->month)
            ->whereYear('sr.created_at', now()->year)
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



    protected function calculateSpakResultsPermenpan142017($responses, $questions)
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

    protected function getDemographicDataForCurrentMonth()
    {
        // Assuming demographics come from identity questions in survey responses
        $identityAnswers = \DB::table('survey_responses as sr')
            ->join('survey_answers as sa', 'sr.id', '=', 'sa.survey_response_id')
            ->join('survey_questions as sq', 'sa.survey_question_id', '=', 'sq.id')
            ->where('sq.survey_type', 'identity')
            ->whereMonth('sr.created_at', now()->month)
            ->whereYear('sr.created_at', now()->year)
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



    public function performanceReport()
    {
        return view('admin.survey.performance-report');
    }

    // Methods for managing survey questions
    public function getIdentityQuestions()
    {
        $questions = SurveyQuestion::where('survey_type', 'identity')
            ->orderBy('order')
            ->get();
        return response()->json($questions);
    }

    public function getSkmQuestions()
    {
        $questions = SurveyQuestion::where('survey_type', 'skm')
            ->with('unsur')
            ->orderBy('order')
            ->get();
        return response()->json($questions);
    }

    public function getSpakQuestions()
    {
        $questions = SurveyQuestion::where('survey_type', 'spak')
            ->with('unsur')
            ->orderBy('order')
            ->get();
        return response()->json($questions);
    }

    public function getQuestion($id)
    {
        $question = SurveyQuestion::with('unsur')->findOrFail($id);
        return response()->json($question);
    }

    public function createQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'field_type' => 'required|string',
            'survey_type' => 'required|in:identity,skm,spak',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'options' => 'nullable|string',
            'category' => 'nullable|string',
            'unsur_id' => ['nullable', 'integer', 'exists:survey_unsur,id', function ($attribute, $value, $fail) use ($request) {
                if (in_array($request->survey_type, ['skm', 'spak']) && is_null($value)) {
                    $fail('The unsur id field is required for skm and spak survey types.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $options = null;
        if ($request->filled('options')) {
            $optionsArray = array_map('trim', explode(',', $request->options));
            $options = json_encode($optionsArray);
        }

        $question = SurveyQuestion::create([
            'question' => $request->question,
            'field_type' => $request->field_type,
            'survey_type' => $request->survey_type,
            'type' => $request->survey_type,
            'is_required' => $request->is_required ?? false,
            'is_active' => $request->is_active ?? true,
            'options' => $options,
            'category' => $request->category,
            'unsur_id' => $request->unsur_id,
            'order' => SurveyQuestion::where('survey_type', $request->survey_type)->max('order') + 1
        ]);

        return response()->json(['success' => true, 'question' => $question], 201);
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = SurveyQuestion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'field_type' => 'required|string',
            'survey_type' => 'required|in:identity,skm,spak',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'options' => 'nullable|string',
            'category' => 'nullable|string',
            'unsur_id' => ['nullable', 'integer', 'exists:survey_unsur,id', function ($attribute, $value, $fail) use ($request) {
                if (in_array($request->survey_type, ['skm', 'spak']) && is_null($value)) {
                    $fail('The unsur id field is required for skm and spak survey types.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $options = null;
        if ($request->filled('options')) {
            $optionsArray = array_map('trim', explode(',', $request->options));
            $options = json_encode($optionsArray);
        }

        $question->update([
            'question' => $request->question,
            'field_type' => $request->field_type,
            'survey_type' => $request->survey_type,
            'type' => $request->survey_type,
            'is_required' => $request->is_required ?? false,
            'is_active' => $request->is_active ?? true,
            'options' => $options,
            'category' => $request->category,
            'unsur_id' => $request->unsur_id,
        ]);

        return response()->json(['success' => true, 'question' => $question]);
    }

    public function deleteQuestion($id)
    {
        $question = SurveyQuestion::findOrFail($id);
        $question->delete();

        return response()->json(['success' => true]);
    }

    // Methods for managing survey editions
    public function getEditions()
    {
        $editions = SurveyEdition::orderBy('year', 'desc')->orderBy('created_at', 'desc')->get();
        return response()->json($editions);
    }

    public function createEdition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:Q1,Q2,Q3,Q4',
            'year' => 'required|integer|min:1900|max:2100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quarter = $request->period;
        $year = $request->year;

        $start_date = now()->setYear($year);
        $end_date = now()->setYear($year);

        if ($quarter == 'Q1') {
            $start_date->setMonth(1)->startOfMonth();
            $end_date->setMonth(3)->endOfMonth();
        } elseif ($quarter == 'Q2') {
            $start_date->setMonth(4)->startOfMonth();
            $end_date->setMonth(6)->endOfMonth();
        } elseif ($quarter == 'Q3') {
            $start_date->setMonth(7)->startOfMonth();
            $end_date->setMonth(9)->endOfMonth();
        } elseif ($quarter == 'Q4') {
            $start_date->setMonth(10)->startOfMonth();
            $end_date->setMonth(12)->endOfMonth();
        }

        $name = "Triwulan " . substr($quarter, 1) . " " . $year;

        // If the edition is being activated, deactivate all other editions first
        if ($request->is_active) {
            SurveyEdition::query()->update(['is_active' => false]);
        }

        $edition = SurveyEdition::create([
            'name' => $name,
            'type' => 'quarterly',
            'period' => $quarter,
            'year' => $year,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_active' => $request->is_active ?? false,
        ]);

        return response()->json(['success' => true, 'edition' => $edition], 201);
    }

    public function updateEdition(Request $request, $id)
    {
        $edition = SurveyEdition::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:monthly,quarterly,yearly',
            'period' => 'required|string',
            'year' => 'required|integer|min:1900|max:2100',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If the edition is being activated, deactivate all other editions first
        if ($request->is_active) {
            SurveyEdition::where('id', '!=', $id)->update(['is_active' => false]);

            // Check if an archive for this edition already exists, if not, create it
            $archive = SurveyArchive::where('survey_edition_id', $id)->first();

            if (!$archive) {
                SurveyArchive::create([
                    'survey_edition_id' => $id,
                    'type' => 'skm', // Assuming SKM for now, this might need to be more dynamic
                    'is_quarterly_archive' => true, // Assuming quarterly, this might need to be more dynamic
                    'year' => $edition->year,
                    'quarter' => $edition->period,
                    'data' => json_encode([]), // Empty data initially
                ]);
            }
        }

        $edition->update([
            'name' => $request->name,
            'type' => $request->type,
            'period' => $request->period,
            'year' => $request->year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'is_active' => $request->is_active ?? false,
        ]);

        return response()->json(['success' => true, 'edition' => $edition]);
    }

    public function deleteEdition($id)
    {
        $edition = SurveyEdition::findOrFail($id);
        $edition->delete();

        return response()->json(['success' => true]);
    }

    // Methods for managing survey unsurs
    public function getUnsurs(Request $request)
    {
        $surveyType = $request->input('survey_type', 'skm');
        $unsurs = SurveyUnsur::where('survey_type', $surveyType)
            ->orderBy('order')
            ->get();
        
        return response()->json($unsurs);
    }

    public function createUnsur(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:survey_unsur,code',
            'survey_type' => 'required|in:identity,skm,spak',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unsur = SurveyUnsur::create([
            'name' => $request->name,
            'code' => $request->code,
            'survey_type' => $request->survey_type,
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['success' => true, 'unsur' => $unsur], 201);
    }

    public function updateUnsur(Request $request, $id)
    {
        $unsur = SurveyUnsur::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:survey_unsur,code,' . $unsur->id,
            'survey_type' => 'required|in:identity,skm,spak',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unsur->update([
            'name' => $request->name,
            'code' => $request->code,
            'survey_type' => $request->survey_type,
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['success' => true, 'unsur' => $unsur]);
    }

    public function deleteUnsur($id)
    {
        $unsur = SurveyUnsur::findOrFail($id);
        $unsur->delete();

        return response()->json(['success' => true]);
    }
}