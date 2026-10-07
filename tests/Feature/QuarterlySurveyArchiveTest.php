<?php

namespace Tests\Feature;

use App\Models\SurveyAnswer;
use App\Models\SurveyArchive;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Arsip rekap survei per kuartal (app:create-quarterly-survey-archives). */
class QuarterlySurveyArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    /** One SKM response rating every SKM question $rating, created at $at. */
    private function response(string $at, int $rating): void
    {
        $response = SurveyResponse::create(['survey_id' => \App\Models\Survey::firstOrFail()->id, 'completed_at' => $at]);
        $response->forceFill(['created_at' => $at])->saveQuietly();

        foreach (SurveyQuestion::where('survey_type', 'skm')->get() as $question) {
            SurveyAnswer::create(['survey_response_id' => $response->id, 'survey_question_id' => $question->id, 'rating_value' => $rating]);
        }
    }

    public function test_quarter_is_archived_with_permenpan_scores_including_its_last_day(): void
    {
        $this->assertGreaterThan(0, SurveyQuestion::where('survey_type', 'skm')->count());
        $this->response('2026-07-01 08:00:00', 4);
        $this->response('2026-09-30 16:30:00', 3); // last day of Q3, in the afternoon
        $this->response('2026-10-01 08:00:00', 1); // next quarter

        $this->artisan('app:create-quarterly-survey-archives', ['--type' => 'skm', '--year' => 2026, '--quarter' => 'Q3'])->assertSuccessful();

        $archive = SurveyArchive::where('type', 'skm')->where('quarter', 'Q3')->where('year', 2026)->sole();
        $this->assertTrue($archive->is_quarterly_archive);
        $this->assertSame(2, $archive->calculated_values['total_respondents']);
        $this->assertEquals(3.5, $archive->calculated_values['average']);
        $this->assertEquals(83.33, $archive->calculated_values['percentage']);
        $this->assertSame('2026-09-30', $archive->data['end_date']);
    }

    public function test_an_archived_quarter_is_not_archived_twice(): void
    {
        $this->response('2026-05-10 09:00:00', 4);

        $this->artisan('app:create-quarterly-survey-archives', ['--type' => 'skm', '--year' => 2026, '--quarter' => 'Q2'])->assertSuccessful();
        $this->artisan('app:create-quarterly-survey-archives', ['--type' => 'skm', '--year' => 2026, '--quarter' => 'Q2'])
            ->expectsOutputToContain('already exists')->assertSuccessful();

        $this->assertSame(1, SurveyArchive::where('type', 'skm')->where('quarter', 'Q2')->count());
    }

    public function test_quarters_without_responses_are_skipped(): void
    {
        $this->artisan('app:create-quarterly-survey-archives', ['--type' => 'spak', '--year' => 2026, '--quarter' => 'Q1'])
            ->expectsOutputToContain('No responses found')->assertSuccessful();

        $this->assertSame(0, SurveyArchive::where('type', 'spak')->where('quarter', 'Q1')->count());
    }
}
