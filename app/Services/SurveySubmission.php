<?php

namespace App\Services;

use App\Mail\ThankYouSurveyMail;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyEdition;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Survei SKM & SPAK: validation of each step and saving a completed survey,
 * shared by the step-by-step web form and the API.
 */
class SurveySubmission
{
    public const MESSAGES = [
        'required' => ':attribute wajib diisi',
        'in' => 'Pilihan :attribute tidak valid',
        'email' => ':attribute harus berupa alamat email yang valid',
        'regex' => ':attribute tidak valid (gunakan angka, 8-20 karakter)',
        'exists' => ':attribute tidak ditemukan',
    ];

    /**
     * Rules for the identity step under "$prefix.{question id}".
     *
     * @return array{0: array, 1: array} rules and attribute names
     */
    public static function identityRules(string $prefix = 'answers'): array
    {
        $rules = [];
        $attributes = [];

        foreach (SurveyQuestion::where('type', 'identity')->active()->get() as $question) {
            $key = "{$prefix}.{$question->id}";
            $rule = [$question->is_required ? 'required' : 'nullable', 'string', 'max:255'];

            if (in_array($question->field_type, ['select', 'radio'], true) && $question->options) {
                $rule[] = Rule::in((array) $question->options);
            } elseif ($question->field_type === 'email') {
                $rule[] = 'email:rfc';
            } elseif ($question->field_type === 'tel') {
                $rule[] = 'regex:/^[0-9+\-\s()]{8,20}$/';
            } elseif (stripos($question->question, 'tiket') !== false) {
                $rule[] = 'exists:tickets,ticket_number';
            }

            $rules[$key] = $rule;
            $attributes[$key] = $question->question;
        }

        return [$rules, $attributes];
    }

    /** Rules for an SKM/SPAK step: each answer must be one of the question's options. */
    public static function ratingRules(string $type, string $prefix = 'answers'): array
    {
        $rules = [];
        foreach (SurveyQuestion::active()->byType($type)->get() as $question) {
            $rules["{$prefix}.{$question->id}"] = [
                $question->is_required ? 'required' : 'nullable',
                Rule::in((array) $question->options),
            ];
        }

        return $rules;
    }

    /** Keep only answers to active questions of this type, without blanks. */
    public static function only(string $type, array $answers): array
    {
        $ids = SurveyQuestion::active()->byType($type)->pluck('id')->all();

        return array_filter(array_intersect_key($answers, array_flip($ids)), fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Save a completed survey for the open edition. Rating answers score
     * 1..n by option position (Permenpan RB 14/2017).
     *
     * @param  array<int, string>  $identity  question id => answer
     */
    public function save(SurveyEdition $edition, array $identity, array $skm, array $spak, ?string $suggestions = null, ?int $userId = null, ?string $ip = null): SurveyResponse
    {
        $response = DB::transaction(function () use ($edition, $identity, $skm, $spak, $suggestions, $userId, $ip) {
            $survey = Survey::where('is_active', true)->first() ?? Survey::create([
                'name' => 'Survey Kepuasan Masyarakat & Persepsi Anti Korupsi ' . now()->year,
                'description' => 'Survey tahunan untuk mengukur IKM dan IPAK sesuai Permenpan RB No. 14 Tahun 2017',
                'type' => 'other',
                'is_active' => true,
                'start_date' => now(),
            ]);

            // "+" keeps the question-id keys; spreading (...) would renumber them.
            $answers = $identity + $skm + $spak;
            $questions = SurveyQuestion::whereIn('id', array_keys($answers))->get()->keyBy('id');

            $ticketQuestion = $questions->first(fn (SurveyQuestion $q) => $q->type === 'identity' && ($q->field_type === 'ticket_number' || stripos($q->question, 'tiket') !== false));
            $ticketId = $ticketQuestion ? Ticket::where('ticket_number', $answers[$ticketQuestion->id] ?? null)->value('id') : null;

            $response = SurveyResponse::create([
                'survey_id' => $survey->id,
                'survey_edition_id' => $edition->id,
                'user_id' => $userId,
                'ticket_id' => $ticketId,
                'ip_address' => $ip,
                'completed_at' => now(),
                'comments' => $suggestions,
            ]);

            foreach ($answers as $questionId => $answer) {
                if (! $question = $questions->get($questionId)) {
                    continue;
                }

                $position = in_array($question->type, ['skm', 'spak'], true)
                    ? array_search($answer, (array) $question->options, true)
                    : false;

                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'selected_option' => is_array($answer) ? json_encode($answer) : $answer,
                    'answer_text' => is_string($answer) ? $answer : null,
                    'rating_value' => $position === false ? null : $position + 1,
                ]);
            }

            return $response;
        });

        if ($response->ticket_id && ($email = Ticket::find($response->ticket_id)?->email)) {
            Mail::to($email)->send(new ThankYouSurveyMail($response));
        }

        return $response;
    }
}
