<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Survey answers are kept as a browser draft so an interrupted survey can be resumed. */
class SurveyDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_form_keeps_a_draft_without_typed_identity_fields(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();

        $this->get(route('survey.form'))
            ->assertOk()
            ->assertSee("const KEY = 'ptsp-survey-draft';", false)
            ->assertSee('"step1Form"', false)
            // only choices and suggestion boxes are stored
            ->assertSee("el.type === 'radio' || el.type === 'checkbox' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA'", false);
    }
}
