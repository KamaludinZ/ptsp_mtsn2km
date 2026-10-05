<?php

namespace App\Http\Controllers;

use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Models\SurveyEdition;
use App\Services\SurveySubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SurveyController extends Controller
{
    /**
     * Show survey form - Step 1: Identity
     */
    public function showForm()
    {
        if (! $edition = SurveyEdition::current()) {
            return view('survey.closed');
        }

        // Get identity questions
        $identityQuestions = SurveyQuestion::where('type', 'identity')
            ->active()
            ->orderBy('order')
            ->get();

        return view('survey.form-step1', compact('identityQuestions', 'edition'));
    }

    /**
     * Store Step 1 (Identity) and proceed to Step 2
     */
    public function storeStep1(Request $request)
    {
        if (! SurveyEdition::current()) {
            return redirect()->route('survey.form');
        }

        [$rules, $attributes] = SurveySubmission::identityRules();

        $validated = $request->validate(['answers' => 'required|array'] + $rules, [
            'answers.required' => 'Semua pertanyaan identitas wajib diisi',
        ] + SurveySubmission::MESSAGES, $attributes);

        Session::put('survey_step1', SurveySubmission::only('identity', $validated['answers']));

        return redirect()->route('survey.step2');
    }

    /**
     * Show survey form - Step 2: SKM Questions
     */
    public function showStep2()
    {
        if (! SurveyEdition::current()) {
            return redirect()->route('survey.form');
        }

        // Check if step 1 completed
        if (!Session::has('survey_step1')) {
            return redirect()->route('survey.form')->with('error', 'Silakan isi data identitas terlebih dahulu');
        }

        // Get SKM questions
        $skmQuestions = SurveyQuestion::active()
            ->byType('skm')
            ->ordered()
            ->get();

        return view('survey.form-step2', ['skmQuestions' => $skmQuestions, 'edition' => SurveyEdition::current()]);
    }

    /**
     * Store Step 2 (SKM) and proceed to Step 3
     */
    public function storeStep2(Request $request)
    {
        if (! SurveyEdition::current()) {
            return redirect()->route('survey.form');
        }

        // Check if step 1 completed
        if (!Session::has('survey_step1')) {
            return redirect()->route('survey.form')->with('error', 'Silakan isi data identitas terlebih dahulu');
        }

        // Store in session
        Session::put('survey_step2', $this->validateRatingAnswers($request, 'skm', 'Semua pertanyaan SKM wajib diisi'));

        return redirect()->route('survey.step3');
    }

    /**
     * Show survey form - Step 3: SPAK Questions
     */
    public function showStep3()
    {
        if (! SurveyEdition::current()) {
            return redirect()->route('survey.form');
        }

        // Check if step 1 and 2 completed
        if (!Session::has('survey_step1') || !Session::has('survey_step2')) {
            return redirect()->route('survey.form')->with('error', 'Silakan lengkapi tahap sebelumnya');
        }

        // Get SPAK questions
        $spakQuestions = SurveyQuestion::active()
            ->byType('spak')
            ->ordered()
            ->get();

        return view('survey.form-step3', ['spakQuestions' => $spakQuestions, 'edition' => SurveyEdition::current()]);
    }

    /**
     * Store Step 3 (SPAK) and complete survey
     */
    public function storeStep3(Request $request)
    {
        // Responses count toward the edition active at submission time.
        if (! $edition = SurveyEdition::current()) {
            return redirect()->route('survey.form');
        }

        // Check if step 1 and 2 completed
        if (!Session::has('survey_step1') || !Session::has('survey_step2')) {
            return redirect()->route('survey.form')->with('error', 'Silakan lengkapi tahap sebelumnya');
        }

        $request->validate(['spak_suggestions' => 'nullable|string|max:1000']);

        // Store in session
        Session::put('survey_step3', $this->validateRatingAnswers($request, 'spak', 'Semua pertanyaan SPAK wajib diisi'));
        Session::put('spak_suggestions', $request->spak_suggestions ?? null);

        try {
            app(SurveySubmission::class)->save(
                $edition,
                Session::get('survey_step1', []),
                Session::get('survey_step2', []),
                Session::get('survey_step3', []),
                $request->spak_suggestions ?? null,
                auth()->id(),
                $request->ip(),
            );

            Session::forget(['survey_step1', 'survey_step2', 'survey_step3', 'spak_suggestions']);

            return redirect()->route('survey.success')->with('success', 'Terima kasih! Survey Anda telah berhasil disimpan.');
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Maaf, survei belum dapat disimpan. Silakan coba lagi.');
        }
    }

    /**
     * Validate SKM/SPAK answers against the active questions of that type:
     * only known question ids are kept and each answer must be one of the
     * question's options.
     */
    private function validateRatingAnswers(Request $request, string $type, string $requiredMessage): array
    {
        $validated = $request->validate(['answers' => 'required|array'] + SurveySubmission::ratingRules($type), [
            'answers.required' => $requiredMessage,
            'answers.*.required' => 'Pertanyaan ini wajib diisi',
            'answers.*.in' => 'Pilihan jawaban tidak valid',
        ]);

        return SurveySubmission::only($type, $validated['answers']);
    }

    /**
     * Show success page
     */
    public function success()
    {
        return view('survey.success');
    }

    /**
     * Show survey results
     */
    public function results()
    {
        // Calculate IKM
        $skmResponses = SurveyAnswer::whereHas('question', function ($query) {
            $query->where('type', 'skm');
        })->get();

        $totalSkmScore = 0;
        $totalSkmCount = 0;

        foreach ($skmResponses as $answer) {
            $value = $this->getAnswerValue($answer->selected_option);
            $totalSkmScore += $value;
            $totalSkmCount++;
        }

        $ikm = $totalSkmCount > 0 ? ($totalSkmScore / $totalSkmCount) * 25 : 0;
        $ikmCategory = $this->getIKMCategory($ikm);

        // Calculate IPAK
        $spakResponses = SurveyAnswer::whereHas('question', function ($query) {
            $query->where('type', 'spak');
        })->get();

        $totalSpakScore = 0;
        $maxSpakScore = $spakResponses->count() * 4;

        foreach ($spakResponses as $answer) {
            $value = $this->getAnswerValue($answer->selected_option);
            $totalSpakScore += $value;
        }

        $ipak = $maxSpakScore > 0 ? ($totalSpakScore / $maxSpakScore) * 100 : 0;
        $ipakCategory = $this->getIPAKCategory($ipak);

        $totalResponses = SurveyResponse::count();

        return view('survey.results', compact(
            'ikm',
            'ikmCategory',
            'ipak',
            'ipakCategory',
            'totalResponses'
        ));
    }

    /**
     * Get numeric value from answer option
     */
    private function getAnswerValue($option)
    {
        // Map answer options to values
        $valueMap = [
            // SKM values
            'Tidak Sesuai' => 1, 'Kurang Sesuai' => 2, 'Sesuai' => 3, 'Sangat Sesuai' => 4,
            'Tidak Mudah' => 1, 'Kurang Mudah' => 2, 'Mudah' => 3, 'Sangat Mudah' => 4,
            'Tidak Cepat' => 1, 'Kurang Cepat' => 2, 'Cepat' => 3, 'Sangat Cepat' => 4,
            'Tidak Bagus' => 1, 'Kurang Bagus' => 2, 'Bagus' => 3, 'Sangat Bagus' => 4,
            'Tidak Mampu' => 1, 'Kurang Mampu' => 2, 'Mampu' => 3, 'Sangat Mampu' => 4,
            'Tidak Sopan' => 1, 'Kurang Sopan' => 2, 'Sopan' => 3, 'Sangat Sopan' => 4,
            'Tidak Jelas' => 1, 'Kurang Jelas' => 2, 'Jelas' => 3, 'Sangat Jelas' => 4,
            'Selalu Tidak Sesuai' => 1, 'Terkadang Sesuai' => 2, 'Sesuai' => 3, 'Selalu Sesuai' => 4,

            // SPAK values
            'Sangat sering' => 1, 'Sering' => 2, 'Jarang' => 3, 'Tidak Pernah' => 4,
            'Tidak Transparan' => 1, 'Kurang Transparan' => 2, 'Transparan' => 3, 'Sangat Transparan' => 4,
        ];

        return $valueMap[$option] ?? 0;
    }

    /**
     * Get IKM category based on score
     */
    private function getIKMCategory($score)
    {
        if ($score >= 88.31 && $score <= 100) return 'A - Sangat Baik';
        if ($score >= 76.61 && $score <= 88.30) return 'B - Baik';
        if ($score >= 65.00 && $score <= 76.60) return 'C - Kurang Baik';
        if ($score >= 25.00 && $score <= 64.99) return 'D - Tidak Baik';
        return 'N/A';
    }

    /**
     * Get IPAK category based on score
     */
    private function getIPAKCategory($score)
    {
        if ($score >= 80) return 'Sangat Baik';
        if ($score >= 60 && $score < 80) return 'Baik';
        if ($score >= 40 && $score < 60) return 'Cukup';
        if ($score < 40) return 'Perlu Perbaikan';
        return 'N/A';
    }
}
