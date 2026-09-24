<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * End-to-end checks of the main PTSP flows: online portal, complaints,
 * SKM/SPAK survey, front desk and back office.
 */
class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        foreach (['admin', 'front_desk', 'back_office', 'kepala_tu', 'umum', 'siswa'] as $role) {
            Role::findOrCreate($role);
        }

        foreach (['frontdesk.access', 'backoffice.access', 'supervision.access'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    private function staff(string $role, ?string $permission = null): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($role);

        if ($permission) {
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    public function test_user_can_register_and_login(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp_number' => '081234567890',
            'user_type' => 'umum',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_service_catalog_lists_services_for_the_user_type(): void
    {
        $public = Service::factory()->create(['name' => 'Legalisir Ijazah', 'user_types_allowed' => ['umum', 'alumni']]);
        $studentOnly = Service::factory()->create(['name' => 'Surat Siswa Aktif', 'user_types_allowed' => ['siswa']]);

        $this->get('/services')
            ->assertOk()
            ->assertSee($public->name)
            ->assertDontSee($studentOnly->name);

        $student = User::factory()->create(['user_type' => 'siswa']);

        $this->actingAs($student)->get('/services')
            ->assertOk()
            ->assertSee($public->name)
            ->assertSee($studentOnly->name);
    }

    public function test_user_can_apply_for_service(): void
    {
        $user = User::factory()->create(['user_type' => 'umum']);
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        $this->actingAs($user)->get("/services/{$service->slug}/apply")->assertOk();

        $response = $this->actingAs($user)->post("/services/{$service->slug}/apply", [
            'description' => 'Mohon legalisir ijazah sebanyak 3 lembar.',
        ]);

        $ticket = Ticket::where('user_id', $user->id)->where('service_id', $service->id)->first();

        $this->assertNotNull($ticket);
        $this->assertSame('online', $ticket->mode);
        $response->assertRedirect(route('onlineportal.application.success', $ticket->ticket_number));
    }

    public function test_public_can_submit_complaint_and_track_it(): void
    {
        $response = $this->post('/complaints/submit', [
            'complaint_type' => 'complaint',
            'reporter_name' => 'Budi',
            'reporter_email' => 'budi@example.com',
            'complaint_title' => 'Layanan legalisir lambat',
            'complaint_description' => 'Saya menunggu lebih dari 3 jam.',
        ]);

        $complaint = Complaint::where('title', 'Layanan legalisir lambat')->first();

        $this->assertNotNull($complaint);
        $response->assertRedirect(route('supervision.complaint.success', $complaint->complaint_number));

        $this->post('/complaints/track', [
            'complaint_number' => $complaint->complaint_number,
            'reporter_email' => 'budi@example.com',
        ])->assertOk()->assertSee($complaint->complaint_number);

        $this->post('/complaints/track', [
            'complaint_number' => $complaint->complaint_number,
            'reporter_email' => 'orang-lain@example.com',
        ])->assertNotFound();
    }

    public function test_anonymous_whistleblowing_hides_reporter_identity(): void
    {
        $this->post('/whistleblowing', [
            'complaint_title' => 'Dugaan pungutan liar',
            'complaint_description' => 'Ada permintaan uang di luar ketentuan.',
            'anonymous' => '1',
            'reporter_name' => 'Rahasia',
            'reporter_email' => 'rahasia@example.com',
        ])->assertRedirect();

        $report = Complaint::where('title', 'Dugaan pungutan liar')->first();

        $this->assertSame('whistleblowing', $report->complaint_type);
        $this->assertSame('Anonim', $report->reporter_name);
        $this->assertNull($report->reporter_email);
    }

    public function test_user_can_submit_three_step_survey(): void
    {
        Survey::factory()->create(['is_active' => true]);

        $identity = SurveyQuestion::create([
            'type' => 'identity', 'question' => 'Nama Lengkap', 'field_type' => 'text',
            'order' => 1, 'is_required' => true, 'is_active' => true, 'survey_type' => 'identity',
        ]);
        $skm = SurveyQuestion::create([
            'type' => 'skm', 'question' => 'Kesesuaian persyaratan', 'field_type' => 'radio',
            'options' => ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai'],
            'order' => 1, 'is_required' => true, 'is_active' => true, 'survey_type' => 'skm',
        ]);
        $spak = SurveyQuestion::create([
            'type' => 'spak', 'question' => 'Tidak ada pungutan liar', 'field_type' => 'radio',
            'options' => ['Tidak Setuju', 'Kurang Setuju', 'Setuju', 'Sangat Setuju'],
            'order' => 1, 'is_required' => true, 'is_active' => true, 'survey_type' => 'spak',
        ]);

        $this->get('/survey')->assertOk()->assertSee('Nama Lengkap');

        $this->post('/survey/step1', ['answers' => [$identity->id => 'Budi']])
            ->assertRedirect(route('survey.step2'));
        $this->get('/survey/step2')->assertOk()->assertSee('Kesesuaian persyaratan');

        $this->post('/survey/step2', ['answers' => [$skm->id => 'Sangat Sesuai']])
            ->assertRedirect(route('survey.step3'));
        $this->get('/survey/step3')->assertOk()->assertSee('Tidak ada pungutan liar');

        $this->post('/survey/step3', ['answers' => [$spak->id => 'Sangat Setuju']])
            ->assertRedirect(route('survey.success'));

        $this->assertDatabaseCount('survey_responses', 1);
        $this->assertDatabaseCount('survey_answers', 3);
        $this->assertDatabaseHas('survey_answers', ['survey_question_id' => $skm->id, 'rating_value' => 4]);
        $this->assertDatabaseHas('survey_answers', ['survey_question_id' => $spak->id, 'rating_value' => 4]);
    }

    public function test_admin_can_access_control_panel(): void
    {
        $this->actingAs($this->staff('admin'))->get('/cp')->assertOk();
    }

    public function test_visitor_book_accepts_obscured_name_checkbox(): void
    {
        $this->post('/visitor-book/submit-visitor', [
            'name' => 'Siti',
            'phone' => '081234567890',
            'purpose' => 'Rapat komite',
            'obscure_name' => 'on',
        ])->assertRedirect(route('public.visitor.book'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitors', ['name' => 'Siti', 'is_obscured' => true]);
    }

    public function test_front_desk_can_register_offline_service(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        $response = $this->actingAs($this->staff('front_desk', 'frontdesk.access'))->post('/frontdesk/service-application', [
            'service_id' => $service->id,
            'applicant_name' => 'Pak Ahmad',
            'applicant_phone' => '081234567890',
            'applicant_type' => 'umum',
            'description' => 'Legalisir ijazah walk-in.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', ['service_id' => $service->id, 'mode' => 'offline']);
    }

    public function test_back_office_user_can_process_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->staff('back_office', 'backoffice.access'))
            ->from('/backoffice/dashboard')
            ->post("/backoffice/tickets/{$ticket->id}/status", [
                'status' => 'in_process',
                'notes' => 'Berkas lengkap, diproses.',
            ])->assertRedirect('/backoffice/dashboard');

        $this->assertSame('in_process', $ticket->fresh()->status);
    }

    public function test_applicants_cannot_open_staff_areas(): void
    {
        $applicant = User::factory()->create(['user_type' => 'umum']);
        $applicant->assignRole('umum');
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $this->actingAs($applicant)->get('/backoffice/dashboard')->assertForbidden();
        $this->actingAs($applicant)->get('/frontdesk/dashboard')->assertForbidden();
        $this->actingAs($applicant)->get('/supervision/management')->assertForbidden();
        $this->actingAs($applicant)->get('/supervision/performance')->assertForbidden();
        $this->actingAs($applicant)
            ->post("/backoffice/tickets/{$ticket->id}/status", ['status' => 'completed', 'notes' => 'x'])
            ->assertForbidden();

        $this->assertSame('submitted', $ticket->fresh()->status);
    }

    public function test_ticket_tracking_does_not_expose_applicant_data(): void
    {
        $applicant = User::factory()->create(['name' => 'Rahasia Pemohon', 'whatsapp_number' => '0899999']);
        $ticket = Ticket::factory()->create(['user_id' => $applicant->id, 'status' => 'in_process']);

        $response = $this->post('/tracking', ['ticket_number' => $ticket->ticket_number])
            ->assertOk()
            ->assertJsonPath('ticket_number', $ticket->ticket_number)
            ->assertJsonPath('status', 'in_process');

        $this->assertStringNotContainsString($applicant->email, $response->getContent());
        $this->assertStringNotContainsString('Rahasia Pemohon', $response->getContent());
        $this->assertStringNotContainsString('0899999', $response->getContent());
    }
}
