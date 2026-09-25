<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The service flow from the design document: back office verifies (Modul 7),
 * a leader approves (Modul 8), the product is finished and handed over
 * (Modul 9), and complaints are followed up to a reply (Modul 10).
 */
class ServiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($role);

        return $user;
    }

    public function test_ticket_gets_a_target_date_from_the_service_standard(): void
    {
        $service = Service::factory()->create(['processing_time' => '1-3 hari kerja']);
        $ticket = Ticket::factory()->create(['service_id' => $service->id, 'status' => 'submitted']);

        $this->assertSame(now()->addWeekdays(3)->toDateString(), $ticket->estimated_completion_date->toDateString());
    }

    public function test_leader_approves_a_verified_ticket_and_back_office_completes_it(): void
    {
        $service = Service::factory()->create(['approval_roles' => null, 'approval_users' => null]);
        $ticket = Ticket::factory()->create([
            'service_id' => $service->id,
            'status' => 'submitted',
            'mode' => 'offline',
            'approval_required' => true,
        ]);
        $officer = $this->staff('back_office');
        $headmaster = $this->staff('kepala_sekolah');

        // Not verified yet: nothing for the leader to decide.
        $this->actingAs($headmaster)->get('/pimpinan/persetujuan')->assertOk()->assertDontSee($ticket->ticket_number);

        $this->actingAs($officer)->post("/backoffice/tickets/{$ticket->id}/status", ['status' => 'verified', 'notes' => 'Berkas lengkap.']);

        // The back office cannot approve or finish it on the leader's behalf.
        $this->actingAs($officer)->post("/backoffice/tickets/{$ticket->id}/status", ['status' => 'approved', 'notes' => 'x'])
            ->assertSessionHasErrors('status');
        $this->actingAs($officer)->post("/backoffice/tickets/{$ticket->id}/status", ['status' => 'completed', 'notes' => 'x'])
            ->assertSessionHasErrors('status');
        $this->actingAs($officer)->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'approve', 'signature_type' => 'tte'])
            ->assertForbidden();
        $this->assertSame('verified', $ticket->fresh()->status);

        $this->actingAs($headmaster)->get('/pimpinan/persetujuan')->assertOk()->assertSee($ticket->ticket_number);
        $this->actingAs($headmaster)->get('/pimpinan')->assertOk()->assertSee('1</strong> permohonan menunggu persetujuan Anda', false);

        $this->actingAs($headmaster)->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'approve'])
            ->assertSessionHasErrors('signature_type');
        $this->actingAs($headmaster)->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'approve', 'signature_type' => 'ttd'])
            ->assertRedirect('/pimpinan/persetujuan');

        $ticket->refresh();
        $this->assertSame('approved', $ticket->status);
        $this->assertSame('approved', $ticket->approval_status);
        $this->assertSame('ttd', $ticket->signature_type);
        $this->assertSame($headmaster->id, $ticket->approved_by);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $ticket->id, 'action' => 'approved', 'to_status' => 'approved']);

        // Deciding twice is refused.
        $this->actingAs($headmaster)->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'reject', 'notes' => 'x'])
            ->assertStatus(409);

        $this->actingAs($officer)->post("/backoffice/tickets/{$ticket->id}/status", ['status' => 'completed', 'notes' => 'Selesai.'])
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame('completed', $ticket->status);
        $this->assertNotNull($ticket->actual_completion_date);
        $this->assertTrue($ticket->ready_for_pickup);

        // Front desk hands the product over at the counter.
        $this->actingAs($this->staff('front_desk'))->post("/frontdesk/tickets/{$ticket->id}/hand-over")->assertRedirect();
        $this->assertFalse($ticket->fresh()->ready_for_pickup);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $ticket->id, 'action' => 'picked_up']);
    }

    public function test_rejection_needs_a_reason_and_closes_the_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => true]);
        $leader = $this->staff('kepala_tu');

        $this->actingAs($leader)->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'reject'])
            ->assertSessionHasErrors('notes');

        $this->actingAs($leader)->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'reject', 'notes' => 'Data tidak sesuai.'])
            ->assertRedirect();

        $this->assertSame('rejected', $ticket->fresh()->status);
        $this->assertSame('Data tidak sesuai.', $ticket->fresh()->approval_notes);
    }

    public function test_service_approvers_limit_who_can_decide(): void
    {
        $service = Service::factory()->create(['approval_roles' => ['kepala_tu']]);
        $ticket = Ticket::factory()->create(['service_id' => $service->id, 'status' => 'verified', 'approval_required' => true]);

        $this->actingAs($this->staff('kepala_sekolah'))->get('/pimpinan/persetujuan')->assertDontSee($ticket->ticket_number);
        $this->actingAs($this->staff('kepala_sekolah'))
            ->post("/pimpinan/persetujuan/{$ticket->id}", ['action' => 'approve', 'signature_type' => 'tte'])
            ->assertForbidden();

        $this->actingAs($this->staff('kepala_tu'))->get('/pimpinan/persetujuan')->assertSee($ticket->ticket_number);
    }

    public function test_supervisor_follows_up_a_complaint_until_the_reporter_sees_the_reply(): void
    {
        $complaint = Complaint::factory()->create([
            'complaint_type' => 'complaint',
            'status' => 'submitted',
            'reporter_email' => 'warga@example.com',
        ]);
        $supervisor = $this->staff('supervisor');

        $this->actingAs($supervisor)->get('/supervision/management')->assertOk()->assertSee($complaint->complaint_number);
        $this->actingAs($supervisor)->get("/admin/complaints/{$complaint->id}")->assertOk()->assertSee($complaint->title);

        // A report cannot be closed without a reply to the reporter.
        $this->actingAs($supervisor)->put("/admin/complaints/{$complaint->id}", ['status' => 'resolved', 'priority' => 'normal'])
            ->assertSessionHasErrors('response');

        $this->actingAs($supervisor)->put("/admin/complaints/{$complaint->id}", [
            'status' => 'resolved',
            'priority' => 'high',
            'assigned_to' => $supervisor->id,
            'response' => 'Petugas loket sudah ditambah pada jam sibuk.',
        ])->assertRedirect("/admin/complaints/{$complaint->id}");

        $complaint->refresh();
        $this->assertSame('resolved', $complaint->status);
        $this->assertSame($supervisor->id, $complaint->resolved_by);
        $this->assertNotNull($complaint->resolved_at);

        $this->post('/complaints/track', [
            'complaint_number' => $complaint->complaint_number,
            'reporter_email' => 'warga@example.com',
        ])->assertOk()->assertSee('Selesai')->assertSee('Petugas loket sudah ditambah pada jam sibuk.');
    }

    public function test_dashboards_show_each_role_its_own_recap(): void
    {
        Ticket::factory()->create(['status' => 'submitted', 'mode' => 'offline']);
        Ticket::factory()->create(['status' => 'completed', 'mode' => 'online']);

        $this->actingAs($this->staff('back_office'))->get('/backoffice/dashboard')
            ->assertOk()->assertSee('Alur permohonan')->assertSee('Ketepatan waktu');
        $this->actingAs($this->staff('front_desk'))->get('/frontdesk/dashboard')
            ->assertOk()->assertSee('Buku tamu')->assertSee('Siap diambil');
        $this->actingAs($this->staff('kepala_sekolah'))->get('/pimpinan')
            ->assertOk()->assertSee('Kinerja per layanan')->assertSee('IKM');
        $this->actingAs($this->staff('supervisor'))->get('/supervision/performance')
            ->assertOk()->assertSee('Kinerja per layanan');
        $this->actingAs($this->staff('admin'))->get('/admin')->assertRedirect('/cp');

        $applicant = User::factory()->create(['user_type' => 'umum']);
        Ticket::factory()->create(['user_id' => $applicant->id, 'status' => 'completed']);
        $this->actingAs($applicant)->get('/portal/dashboard')
            ->assertOk()->assertSee('Permohonan terbaru')->assertSee(route('survey.form'), false);
    }
}
