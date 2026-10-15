<?php

namespace Tests\Feature;

use App\Http\Controllers\SurveyController;
use App\Models\SurveyAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Hasil survei publik: counted in SQL and cached, same figures as answer by answer. */
class SurveyPublicResultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_results_match_the_answers_and_are_cached(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();

        $response = \App\Models\SurveyResponse::create(['survey_id' => \App\Models\Survey::query()->value('id'), 'completed_at' => now()]);
        foreach (['skm' => ['Sangat Sesuai', 'Mudah', 'Kurang Cepat', 'Sesuai'], 'spak' => ['Tidak Pernah', 'Jarang', 'Transparan']] as $type => $options) {
            $question = \App\Models\SurveyQuestion::create(['type' => $type, 'question' => "Uji {$type}", 'field_type' => 'radio', 'order' => 99, 'is_active' => true]);
            foreach ($options as $option) {
                SurveyAnswer::create(['survey_response_id' => $response->id, 'survey_question_id' => $question->id, 'selected_option' => $option]);
            }
        }

        $value = new \ReflectionMethod(SurveyController::class, 'getAnswerValue');
        $scores = fn (string $type) => SurveyAnswer::whereHas('question', fn ($q) => $q->where('type', $type))->get()
            ->map(fn ($answer) => $value->invoke(new SurveyController(), $answer->selected_option));
        $skm = $scores('skm');
        $spak = $scores('spak');
        $this->assertNotEmpty($skm, 'seeded survey answers expected');

        $this->get(route('survey.results'))
            ->assertOk()
            ->assertViewHas('ikm', fn ($ikm) => abs($ikm - $skm->avg() * 25) < 0.0001)
            ->assertViewHas('ipak', fn ($ipak) => abs($ipak - ($spak->isEmpty() ? 0 : $spak->sum() / ($spak->count() * 4) * 100)) < 0.0001);

        $this->assertTrue(Cache::has('survey_public_results'));
    }
}
