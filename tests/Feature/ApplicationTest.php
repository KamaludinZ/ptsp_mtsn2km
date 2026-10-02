<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\Survey;
use App\Models\SurveyEdition;
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

    public function test_civitas_registers_with_the_shared_code_and_picks_a_status(): void
    {
        Role::findOrCreate('guru');
        $form = [
            'is_civitas' => '1',
            'name' => 'Bu Guru',
            'email' => 'guru@example.com',
            'whatsapp_number' => '081234567890',
            'civitas_type' => 'guru',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // No code set yet: civitas registration is closed.
        $this->post('/register', $form + ['registration_code' => 'APAPUN'])->assertSessionHasErrors('registration_code');

        \App\Support\CivitasRegistration::setCode('MTSN2-2026');

        $this->post('/register', $form + ['registration_code' => 'SALAH'])->assertSessionHasErrors('registration_code');
        $this->post('/register', ['civitas_type' => 'admin'] + $form + ['registration_code' => 'MTSN2-2026'])
            ->assertSessionHasErrors('civitas_type');
        $this->assertGuest();

        $this->post('/register', $form + ['registration_code' => 'MTSN2-2026'])->assertSessionHasNoErrors();

        $user = User::where('email', 'guru@example.com')->first();
        $this->assertSame('guru', $user->user_type);
        $this->assertTrue($user->hasRole('guru'));
    }

    public function test_service_catalog_lists_services_for_the_user_type(): void
    {
        $public = Service::factory()->create(['name' => 'Legalisir Ijazah', 'user_types_allowed' => ['umum', 'alumni']]);
        $studentOnly = Service::factory()->create(['name' => 'Surat Siswa Aktif', 'user_types_allowed' => ['siswa']]);

        $this->get('/services')
            ->assertOk()
            ->assertSee($public->name)
            ->assertDontSee($studentOnly->name);

        $category = \App\Models\ServiceCategory::create(['name' => 'Akademik', 'is_active' => true]);
        $public->categories()->attach($category);

        $this->get("/services/{$public->slug}")
            ->assertOk()
            ->assertSee($public->name);
        $this->get("/services/{$studentOnly->slug}")->assertNotFound();

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

        // The catalogue's "apply" link leads to the applicant portal
        $this->actingAs($user)->get("/services/{$service->slug}/apply")
            ->assertRedirect('/portal/ajukan?layanan=' . $service->slug);
        $this->actingAs($user)->get('/portal/ajukan?layanan=' . $service->slug)->assertOk()->assertSee($service->name);

        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('portal'));
        \Livewire\Livewire::withQueryParams(['layanan' => $service->slug])
            ->test(\App\Filament\Portal\Pages\ApplyService::class)
            ->fillForm(['description' => 'Mohon legalisir ijazah sebanyak 3 lembar.', 'priority' => 'normal'])
            ->call('submit')
            ->assertHasNoFormErrors();

        $ticket = Ticket::where('user_id', $user->id)->where('service_id', $service->id)->first();

        $this->assertNotNull($ticket);
        $this->assertSame('online', $ticket->mode);
        $this->assertSame('submitted', $ticket->status);
        $this->assertMatchesRegularExpression('/^PTSP-\d{6}-\d{4}$/', $ticket->ticket_number);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $ticket->id, 'action' => 'created']);
    }

    public function test_services_not_open_to_the_applicant_cannot_be_requested(): void
    {
        $user = User::factory()->create(['user_type' => 'umum']);
        $studentOnly = Service::factory()->create(['user_types_allowed' => ['siswa']]);

        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('portal'));
        $this->actingAs($user);
        \Livewire\Livewire::withQueryParams(['layanan' => $studentOnly->slug])
            ->test(\App\Filament\Portal\Pages\ApplyService::class)
            ->assertRedirect(route('onlineportal.service.catalog'));

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_complaint_and_whistleblowing_forms_live_on_one_page(): void
    {
        $this->get('/complaints')->assertOk()
            ->assertSee(route('supervision.complaint.submit.store'))
            ->assertSee(route('supervision.whistleblowing.submit'));

        $this->get('/complaints/submit')->assertRedirect('/complaints');
        $this->get('/whistleblowing')->assertRedirect('/complaints?tab=whistleblowing');
    }

    public function test_public_sends_a_suggestion_that_only_leadership_reads(): void
    {
        $this->post('/complaints/saran', ['suggestion' => ''])->assertSessionHasErrors('suggestion');

        $this->post('/complaints/saran', ['suggestion' => 'Tambah kursi di ruang tunggu.'])
            ->assertRedirect(route('supervision.complaints.dashboard', ['tab' => 'saran']));

        $suggestion = Complaint::where('complaint_type', 'suggestion')->first();
        $this->assertSame('Tambah kursi di ruang tunggu.', $suggestion->description);
        $this->assertStringStartsWith('SRN-', $suggestion->complaint_number);

        $this->get('/complaints?tab=saran')->assertOk()->assertSee('Kirim Saran');

        Role::findOrCreate('kepala_sekolah');
        $this->actingAs($this->staff('kepala_sekolah'));
        $this->get('/cp/saran')->assertOk()->assertSee('Tambah kursi di ruang tunggu.');
        // Suggestions no longer appear among complaints.
        $this->get("/cp/pengaduan/{$suggestion->id}")->assertNotFound();

        Role::findOrCreate('supervisor');
        $this->actingAs($this->staff('supervisor'));
        $this->get('/cp/saran')->assertForbidden();
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
        $this->assertStringStartsWith('PEM-', $complaint->complaint_number);
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
        $this->assertStringStartsWith('WSB-', $report->complaint_number);
        $this->assertSame('Anonim', $report->reporter_name);
        $this->assertNull($report->reporter_email);
    }

    public function test_user_can_submit_three_step_survey(): void
    {
        Survey::factory()->create(['is_active' => true]);
        $edition = $this->edition('Triwulan 3 2026', true);

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

        $this->get('/survey')->assertOk()->assertSee('Nama Lengkap')->assertSee('Triwulan 3 2026');

        $this->post('/survey/step1', ['answers' => [$identity->id => 'Budi']])
            ->assertRedirect(route('survey.step2'));
        $this->get('/survey/step2')->assertOk()->assertSee('Kesesuaian persyaratan');

        $this->post('/survey/step2', ['answers' => [$skm->id => 'Sangat Sesuai']])
            ->assertRedirect(route('survey.step3'));
        $this->get('/survey/step3')->assertOk()->assertSee('Tidak ada pungutan liar');

        $this->post('/survey/step3', ['answers' => [$spak->id => 'Sangat Setuju']])
            ->assertRedirect(route('survey.success'));

        $this->assertDatabaseCount('survey_responses', 1);
        $this->assertDatabaseHas('survey_responses', ['survey_edition_id' => $edition->id]);
        $this->assertDatabaseCount('survey_answers', 3);
        $this->assertDatabaseHas('survey_answers', ['survey_question_id' => $skm->id, 'rating_value' => 4]);
        $this->assertDatabaseHas('survey_answers', ['survey_question_id' => $spak->id, 'rating_value' => 4]);
    }

    public function test_survey_is_closed_without_an_active_edition(): void
    {
        $this->edition('Triwulan 2 2026', false);

        $this->get('/survey')->assertOk()->assertSee('Survei belum dibuka');
        $this->get('/survey/step2')->assertRedirect(route('survey.form'));
        $this->post('/survey/step3', [])->assertRedirect(route('survey.form'));
        $this->assertDatabaseCount('survey_responses', 0);
    }

    public function test_only_one_survey_edition_is_active(): void
    {
        $q2 = $this->edition('Triwulan 2 2026', true);
        $q3 = $this->edition('Triwulan 3 2026', true);

        $this->assertFalse($q2->fresh()->is_active);
        $this->assertTrue(SurveyEdition::current()->is($q3));

        $q2->fresh()->update(['is_active' => true]);

        $this->assertFalse($q3->fresh()->is_active);
        $this->assertSame(1, SurveyEdition::where('is_active', true)->count());
        $this->get('/survey')->assertSee('Triwulan 2 2026')->assertDontSee('Triwulan 3 2026');
    }

    private function edition(string $name, bool $active): SurveyEdition
    {
        return SurveyEdition::create([
            'name' => $name, 'type' => 'quarterly', 'period' => 'Q' . substr($name, 9, 1), 'year' => 2026,
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_active' => $active,
        ]);
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
            'purpose' => 'Komite',
            'obscure_name' => 'on',
        ])->assertRedirect(route('public.visitor.book'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitors', ['name' => 'Siti', 'purpose' => 'Komite', 'is_obscured' => true]);
        $this->get('/visitor-book')->assertSee('S**i')->assertDontSee('Siti');
    }

    public function test_visitor_book_purpose_other_requires_free_text(): void
    {
        $this->post('/visitor-book/submit-visitor', [
            'name' => 'Rina',
            'phone' => '081234567890',
            'purpose' => 'Lainnya',
        ])->assertSessionHasErrors('purpose_other');

        $this->post('/visitor-book/submit-visitor', [
            'name' => 'Rina',
            'phone' => '081234567890',
            'purpose' => 'Lainnya',
            'purpose_other' => 'Antar dokumen dinas',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitors', ['name' => 'Rina', 'purpose' => 'Antar dokumen dinas']);
    }

    public function test_visitor_book_applicant_picks_a_listed_service_or_other(): void
    {
        $service = Service::factory()->create(['name' => 'Legalisir Ijazah', 'is_active' => true]);

        $this->get('/visitor-book')->assertSee('Legalisir Ijazah');

        $this->post('/visitor-book/submit-applicant', [
            'name' => 'Andi',
            'phone' => '081234567890',
            'target_service' => 'Layanan Palsu',
        ])->assertSessionHasErrors('target_service');

        $this->post('/visitor-book/submit-applicant', [
            'name' => 'Andi',
            'phone' => '081234567890',
            'target_service' => $service->name,
        ])->assertSessionHasNoErrors();

        $this->post('/visitor-book/submit-applicant', [
            'name' => 'Dewi',
            'phone' => '081234567890',
            'target_service' => 'Lainnya',
            'target_service_other' => 'Legalisir rapor',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitors', ['name' => 'Andi', 'purpose' => 'Pemohon Layanan: Legalisir Ijazah']);
        $this->assertDatabaseHas('visitors', ['name' => 'Dewi', 'purpose' => 'Pemohon Layanan: Legalisir rapor']);
    }

    public function test_front_desk_can_register_offline_service(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        $this->actingAs($this->staff('front_desk', 'frontdesk.access'));
        \Livewire\Livewire::test(\App\Filament\Pages\FrontDesk\RegisterService::class)
            ->fillForm([
                'service_id' => $service->id,
                'applicant_name' => 'Pak Ahmad',
                'applicant_phone' => '081234567890',
                'applicant_type' => 'umum',
                'description' => 'Legalisir ijazah walk-in.',
                'priority' => 'normal',
            ])
            ->call('submit')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', ['service_id' => $service->id, 'mode' => 'offline']);
        $this->assertDatabaseHas('users', ['name' => 'Pak Ahmad', 'whatsapp_number' => '081234567890', 'email' => '081234567890@walkin.local']);
    }

    public function test_back_office_user_can_process_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->staff('back_office', 'backoffice.access'));
        \Livewire\Livewire::test(\App\Filament\Resources\TicketResource\Pages\ViewTicket::class, ['record' => $ticket->id])
            ->callAction('changeStatus', ['status' => 'in_process', 'notes' => 'Berkas lengkap, diproses.'])
            ->assertHasNoActionErrors();

        $this->assertSame('in_process', $ticket->fresh()->status);
    }

    public function test_applicants_cannot_open_staff_areas(): void
    {
        $applicant = User::factory()->create(['user_type' => 'umum']);
        $applicant->assignRole('umum');
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        foreach (['/cp', '/cp/tiket', "/cp/tiket/{$ticket->id}", '/cp/kinerja', '/cp/pengaduan', '/cp/visitors'] as $uri) {
            $this->actingAs($applicant)->get($uri)->assertForbidden();
        }

        // Nor someone else's ticket in the portal
        $this->actingAs($applicant)->get("/portal/permohonan/{$ticket->id}")->assertNotFound();

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
