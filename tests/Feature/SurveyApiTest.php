<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\SurveyReport;
use App\Models\SurveyAnswer;
use App\Models\SurveyEdition;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Survei API: the open edition's steps, as on the web form. */
class SurveyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_structure_lists_the_three_steps_of_the_open_edition(): void
    {
        $edition = SurveyEdition::current();
        $this->assertNotNull($edition);

        $response = $this->getJson('/api/publik/survei')->assertOk()->assertJsonPath('edisi.id', $edition->id);
        $steps = collect($response->json('langkah'));

        $this->assertSame(['identitas', 'skm', 'spak'], $steps->pluck('kunci')->all());
        $this->assertCount(SurveyQuestion::active()->byType('skm')->count(), $steps[1]['pertanyaan']);

        SurveyQuestion::byType('skm')->first()->update(['is_active' => false]);
        $this->assertCount(SurveyQuestion::active()->byType('skm')->count(), $this->getJson('/api/publik/survei')->json('langkah.1.pertanyaan'));
    }

    public function test_structure_says_when_no_survey_is_open(): void
    {
        SurveyEdition::query()->update(['is_active' => false]);

        $this->getJson('/api/publik/survei')->assertNotFound();
    }

    /** A valid answer for every active question of a type. */
    private function answers(string $type): array
    {
        return SurveyQuestion::active()->byType($type)->get()->mapWithKeys(fn (SurveyQuestion $q) => [
            $q->id => match (true) {
                (bool) $q->options => last((array) $q->options),
                $q->field_type === 'email' => 'responden@example.test',
                $q->field_type === 'tel' => '081234567890',
                $q->field_type === 'number' => '30',
                stripos($q->question, 'tiket') !== false => null,
                default => 'Jawaban',
            },
        ])->filter(fn ($v) => $v !== null)->all();
    }

    public function test_a_completed_survey_is_saved_through_the_api(): void
    {
        $before = SurveyResponse::count();

        $this->postJson('/api/publik/survei', [
            'identitas' => $this->answers('identity'),
            'skm' => $this->answers('skm'),
            'spak' => $this->answers('spak'),
            'saran' => 'Pertahankan pelayanan.',
        ])->assertCreated();

        $response = SurveyResponse::latest('id')->firstOrFail();
        $this->assertSame($before + 1, SurveyResponse::count());
        $this->assertSame(SurveyEdition::current()->id, $response->survey_edition_id);
        $this->assertSame('Pertahankan pelayanan.', $response->comments);
        // Last option of each rating question scores the top of the scale.
        $this->assertSame(4, (int) SurveyAnswer::where('survey_response_id', $response->id)->whereNotNull('rating_value')->max('rating_value'));
    }

    public function test_invalid_answers_are_refused(): void
    {
        $skm = $this->answers('skm');
        $skm[array_key_first($skm)] = 'Bukan pilihan';

        $this->postJson('/api/publik/survei', ['identitas' => $this->answers('identity'), 'skm' => $skm, 'spak' => $this->answers('spak')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('skm.' . array_key_first($skm));
    }

    public function test_admin_manages_editions_through_the_api(): void
    {
        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $year = now()->year + 1;

        $id = $this->postJson('/api/survei/edisi', ['triwulan' => 'Q2', 'tahun' => $year, 'aktif' => true])
            ->assertCreated()
            ->assertJsonPath('nama', "Triwulan 2 {$year}")
            ->assertJsonPath('mulai', "{$year}-04-01")
            ->json('id');
        $this->assertSame($id, SurveyEdition::current()->id);
        $this->assertSame(1, SurveyEdition::where('is_active', true)->count());

        $this->postJson('/api/survei/edisi', ['triwulan' => 'Q2', 'tahun' => $year])->assertJsonValidationErrors('triwulan');

        $this->putJson('/api/survei/edisi/' . $id, ['aktif' => false])->assertOk()->assertJsonPath('aktif', false);
        $this->assertNull(SurveyEdition::current());

        $this->deleteJson('/api/survei/edisi/' . $id)->assertNoContent();

        $answered = SurveyResponse::whereNotNull('survey_edition_id')->value('survey_edition_id');
        if ($answered) {
            $this->deleteJson('/api/survei/edisi/' . $answered)->assertUnprocessable();
        }

        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/survei/edisi')->assertForbidden();
    }

    public function test_admin_manages_unsur_and_questions_through_the_api(): void
    {
        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        $unsur = $this->postJson('/api/survei/unsur', ['survey_type' => 'skm', 'code' => 'skm-uji', 'name' => 'Kenyamanan ruang tunggu'])->assertCreated()->json('id');
        $this->postJson('/api/survei/unsur', ['survey_type' => 'skm', 'code' => 'skm-uji', 'name' => 'Ganda'])->assertJsonValidationErrors('code');

        $options = ['Tidak nyaman', 'Kurang nyaman', 'Nyaman', 'Sangat nyaman'];
        $question = $this->postJson('/api/survei/pertanyaan', ['type' => 'skm', 'unsur_id' => $unsur, 'question' => 'Bagaimana kenyamanan ruang tunggu?', 'field_type' => 'radio', 'options' => $options])
            ->assertCreated()->json('id');
        $this->postJson('/api/survei/pertanyaan', ['type' => 'skm', 'unsur_id' => $unsur, 'question' => 'x', 'field_type' => 'radio', 'options' => ['Ya', 'Tidak']])
            ->assertJsonValidationErrors('options');
        $this->postJson('/api/survei/pertanyaan', ['type' => 'skm', 'question' => 'x', 'field_type' => 'radio', 'options' => $options])
            ->assertJsonValidationErrors('unsur_id');

        // The new question appears in the public survey.
        $skm = collect($this->getJson('/api/publik/survei')->json('langkah.1.pertanyaan'));
        $this->assertTrue($skm->pluck('id')->contains($question));

        $this->deleteJson('/api/survei/unsur/' . $unsur)->assertUnprocessable();
        $this->putJson('/api/survei/pertanyaan/' . $question, ['is_active' => false])->assertOk();
        $this->deleteJson('/api/survei/pertanyaan/' . $question)->assertNoContent();
        $this->deleteJson('/api/survei/unsur/' . $unsur)->assertNoContent();

        // Answered questions are kept, only switched off.
        $answered = SurveyAnswer::value('survey_question_id');
        if ($answered) {
            $this->deleteJson('/api/survei/pertanyaan/' . $answered)->assertOk();
            $this->assertFalse(SurveyQuestion::find($answered)->is_active);
        }
    }

    public function test_report_aggregates_index_and_quality_grade(): void
    {
        // One respondent answering every SKM question with the best option scores IKM 100.
        $this->postJson('/api/publik/survei', ['identitas' => $this->answers('identity'), 'skm' => $this->answers('skm'), 'spak' => $this->answers('spak')])->assertCreated();
        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/survei/laporan?jenis=skm&periode=month&edisi=' . SurveyEdition::current()->id)
            ->assertOk()
            ->assertJsonPath('indeks', 'IKM')
            ->assertJsonPath('mutu', 'A (Sangat Baik)')
            ->assertJsonPath('responden', 1);
        $this->getJson('/api/survei/laporan?jenis=spak&periode=year')->assertOk()->assertJsonPath('indeks', 'IPAK');
        $this->getJson('/api/survei/laporan?periode=abad')->assertJsonValidationErrors('periode');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/survei/laporan')->assertForbidden();
    }

    public function test_report_downloads_as_pdf_from_the_api_and_the_page(): void
    {
        $this->freezeTime();
        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
        $this->get('/api/survei/laporan?format=pdf&periode=year')->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
        \Livewire\Livewire::test(\App\Filament\Pages\Reports\SurveyReport::class)
            ->set('period', 'year')
            ->callAction('pdf')
            ->assertFileDownloaded('laporan-skm-' . now()->startOfYear()->format('Ymd') . '-' . now()->endOfYear()->format('Ymd') . '.pdf');
    }

    public function test_report_downloads_as_excel_from_the_api(): void
    {
        \Maatwebsite\Excel\Facades\Excel::fake();
        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());

        $this->get('/api/survei/laporan?format=xlsx&periode=year')->assertOk();

        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('laporan-survei-' . now()->startOfYear()->format('Ymd') . '-' . now()->endOfYear()->format('Ymd') . '.xlsx');
    }
}
