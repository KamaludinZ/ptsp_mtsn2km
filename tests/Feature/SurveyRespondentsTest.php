<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Pages\Reports\SurveyRespondents;
use App\Mail\TemplateNotificationMail;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\SurveyReminders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Responden survei: who rated a request, and reminders for completed requests not rated yet. */
class SurveyRespondentsTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();

        $applicant = tap(User::factory()->create(['name' => 'Siti Aminah', 'email' => 'siti@example.test']))->assignRole('umum');
        $this->ticket = app(TicketService::class)->open(Service::availableFor('umum')->firstOrFail(), $applicant, $applicant, 'online', 'Legalisir');
        $this->ticket->forceFill(['status' => 'completed', 'actual_completion_date' => now()])->saveQuietly();
        $this->supervisor = User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail();
        NotificationSetting::store('email', true, ['host' => 'smtp.test', 'port' => 25, 'from_address' => 'ptsp@test']);
        Mail::fake();
    }

    public function test_reminder_template_exists_for_every_channel(): void
    {
        $this->assertSame(2, NotificationTemplate::where('key', 'survey_reminder')->count());
    }

    public function test_supervisors_open_both_tabs(): void
    {
        $this->actingAs($this->supervisor)->get(SurveyRespondents::getUrl())->assertOk()->assertSee('Belum mengisi');
        $this->actingAs($this->supervisor)->get(SurveyRespondents::getUrl(['tab' => 'belum']))->assertOk()->assertSee($this->ticket->ticket_number);
    }

    public function test_front_desk_cannot_open_the_page(): void
    {
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())->get(SurveyRespondents::getUrl())->assertForbidden();
    }

    public function test_reminder_goes_to_the_applicant_with_the_survey_link_once_a_day(): void
    {
        $this->actingAs($this->supervisor);

        Livewire::test(SurveyRespondents::class, ['tab' => 'belum'])
            ->assertCanSeeTableRecords([$this->ticket])
            ->callTableAction('remind', $this->ticket)
            ->assertNotified('1 pengingat survei dikirim');

        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo('siti@example.test')
            && str_contains($mail->body, route('survey.form', ['tiket' => $this->ticket->ticket_number])));
        $this->assertNotNull(SurveyReminders::lastSentAt($this->ticket));

        $this->expectException(TicketActionException::class);
        SurveyReminders::remind($this->ticket, $this->supervisor);
    }

    public function test_rated_requests_leave_the_reminder_list(): void
    {
        $this->ticket->surveyResponses()->create(['survey_id' => \App\Models\Survey::firstOrFail()->id, 'completed_at' => now(), 'user_id' => $this->ticket->user_id]);

        $this->assertFalse(SurveyReminders::unrated()->whereKey($this->ticket->id)->exists());
        $this->actingAs($this->supervisor);
        Livewire::test(SurveyRespondents::class)
            ->assertSee('Siti Aminah')
            ->assertSee($this->ticket->ticket_number);
    }

    public function test_reminders_through_the_api(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->supervisor);

        $this->getJson('/api/survei/belum-mengisi')->assertOk()->assertJsonFragment(['nomor_tiket' => $this->ticket->ticket_number]);
        $this->postJson('/api/survei/pengingat', ['tiket' => [$this->ticket->ticket_number, 'TIDAK-ADA']])
            ->assertOk()
            ->assertJsonPath('terkirim', 1)
            ->assertJsonPath('hasil.0.kanal', ['email'])
            ->assertJsonPath('hasil.1.terkirim', false);
        $this->postJson('/api/survei/pengingat', ['tiket' => [$this->ticket->ticket_number]])->assertJsonPath('terkirim', 0);

        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/survei/belum-mengisi')->assertForbidden();
    }
}
