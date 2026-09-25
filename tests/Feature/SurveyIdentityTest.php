<?php

namespace Tests\Feature;

use App\Models\SurveyQuestion;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_identity_fields_show_red_asterisk_and_are_enforced(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->get('/survey')->assertOk()
            ->assertSee('text-danger', false)
            ->assertSee('No. Telepon')
            ->assertSee('Email Aktif');

        // Nothing submitted: every required question is reported, ticket code is not.
        $required = SurveyQuestion::where('type', 'identity')->where('is_required', true)->pluck('id');
        $this->assertCount(8, $required);

        $response = $this->post('/survey/step1', ['answers' => []]);
        $response->assertSessionHasErrors($required->map(fn ($id) => "answers.$id")->all());
        $response->assertSessionDoesntHaveErrors('answers.8');
    }

    public function test_valid_identity_moves_to_step_two(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $answers = [];
        foreach (SurveyQuestion::where('type', 'identity')->where('is_required', true)->get() as $q) {
            $answers[$q->id] = match ($q->field_type) {
                'email' => 'tamu@example.test',
                'tel' => '081234567890',
                'text' => 'Budi Santoso',
                default => $q->options[0],
            };
        }

        $this->post('/survey/step1', ['answers' => $answers])->assertRedirect(route('survey.step2'));
    }
}
