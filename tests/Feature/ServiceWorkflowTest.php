<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Resources\ComplaintResource\Pages\ViewComplaint;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Widgets;
use App\Models\Complaint;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $this->actingAs($headmaster);
        Livewire::test(Approvals::class)->assertCanNotSeeTableRecords([$ticket]);

        $this->actingAs($officer);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('changeStatus', ['status' => 'verified', 'notes' => 'Berkas lengkap.'])
            ->assertHasNoActionErrors();
        $this->assertSame('verified', $ticket->fresh()->status);

        // The back office cannot approve or finish it on the leader's behalf.
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->assertActionHidden('approve')
            ->assertActionHidden('uploadOutput')
            ->callAction('changeStatus', ['status' => 'completed', 'notes' => 'x']);
        $this->assertSame('verified', $ticket->fresh()->status);

        try {
            app(TicketService::class)->changeStatus($ticket->fresh(), 'approved', 'x', $officer);
            $this->fail('The back office must not be able to approve.');
        } catch (TicketActionException) {
        }
        try {
            app(TicketService::class)->decide($ticket->fresh(), true, $officer, 'tte');
            $this->fail('The back office must not be able to decide.');
        } catch (TicketActionException) {
        }
        $this->assertSame('verified', $ticket->fresh()->status);

        // The leader sees it, must pick a signature type, then approves.
        $this->actingAs($headmaster);
        Livewire::test(Widgets\Leadership\LeadershipStats::class)->assertSee('Menunggu keputusan Anda');
        Livewire::test(Approvals::class)
            ->assertCanSeeTableRecords([$ticket])
            ->callTableAction('approve', $ticket, ['signature_type' => null])
            ->assertHasTableActionErrors(['signature_type' => 'required']);
        Livewire::test(Approvals::class)
            ->callTableAction('approve', $ticket, ['signature_type' => 'ttd'])
            ->assertHasNoTableActionErrors();

        $ticket->refresh();
        $this->assertSame('approved', $ticket->status);
        $this->assertSame('approved', $ticket->approval_status);
        $this->assertSame('ttd', $ticket->signature_type);
        $this->assertSame($headmaster->id, $ticket->approved_by);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $ticket->id, 'action' => 'approved', 'to_status' => 'approved']);

        // Deciding twice is refused.
        $this->expectExceptionRefused(fn () => app(TicketService::class)->decide($ticket->fresh(), false, $headmaster, null, 'x'));

        $this->actingAs($officer);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('changeStatus', ['status' => 'completed', 'notes' => 'Selesai.'])
            ->assertHasNoActionErrors();

        $ticket->refresh();
        $this->assertSame('completed', $ticket->status);
        $this->assertNotNull($ticket->actual_completion_date);
        $this->assertTrue($ticket->ready_for_pickup);

        // Front desk hands the product over at the counter.
        $this->actingAs($this->staff('front_desk'));
        Livewire::test(Widgets\FrontDesk\PickupTickets::class)
            ->assertCanSeeTableRecords([$ticket])
            ->callTableAction('handOver', $ticket);
        $this->assertFalse($ticket->fresh()->ready_for_pickup);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $ticket->id, 'action' => 'picked_up']);
    }

    public function test_rejection_needs_a_reason_and_closes_the_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => true]);
        $this->actingAs($this->staff('kepala_tu'));

        Livewire::test(Approvals::class)
            ->callTableAction('reject', $ticket, ['notes' => ''])
            ->assertHasTableActionErrors(['notes' => 'required']);

        Livewire::test(Approvals::class)
            ->callTableAction('reject', $ticket, ['notes' => 'Data tidak sesuai.'])
            ->assertHasNoTableActionErrors();

        $this->assertSame('rejected', $ticket->fresh()->status);
        $this->assertSame('Data tidak sesuai.', $ticket->fresh()->approval_notes);
    }

    public function test_service_approvers_limit_who_can_decide(): void
    {
        $service = Service::factory()->create(['approval_roles' => ['kepala_tu']]);
        $ticket = Ticket::factory()->create(['service_id' => $service->id, 'status' => 'verified', 'approval_required' => true]);
        $headmaster = $this->staff('kepala_sekolah');

        $this->actingAs($headmaster);
        Livewire::test(Approvals::class)->assertCanNotSeeTableRecords([$ticket]);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])->assertActionHidden('approve');
        $this->expectExceptionRefused(fn () => app(TicketService::class)->decide($ticket, true, $headmaster, 'tte'));

        $this->actingAs($this->staff('kepala_tu'));
        Livewire::test(Approvals::class)->assertCanSeeTableRecords([$ticket]);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])->assertActionVisible('approve');
    }

    public function test_supervisor_follows_up_a_complaint_until_the_reporter_sees_the_reply(): void
    {
        $complaint = Complaint::factory()->create([
            'complaint_type' => 'complaint',
            'status' => 'submitted',
            'reporter_email' => 'warga@example.com',
        ]);
        $supervisor = $this->staff('supervisor');
        $this->actingAs($supervisor);

        Livewire::test(Widgets\Supervision\NewComplaints::class)->assertCanSeeTableRecords([$complaint]);
        $this->get("/cp/pengaduan/{$complaint->id}")->assertOk()->assertSee($complaint->title);

        // A report cannot be closed without a reply to the reporter.
        Livewire::test(ViewComplaint::class, ['record' => $complaint->id])
            ->callAction('followUp', ['status' => 'resolved', 'priority' => 'normal', 'response' => '']);
        $this->assertSame('submitted', $complaint->fresh()->status);

        Livewire::test(ViewComplaint::class, ['record' => $complaint->id])
            ->callAction('followUp', [
                'status' => 'resolved',
                'priority' => 'high',
                'assigned_to' => $supervisor->id,
                'response' => 'Petugas loket sudah ditambah pada jam sibuk.',
            ])
            ->assertHasNoActionErrors();

        $complaint->refresh();
        $this->assertSame('resolved', $complaint->status);
        $this->assertSame($supervisor->id, $complaint->resolved_by);
        $this->assertNotNull($complaint->resolved_at);

        auth()->logout();
        $this->post('/complaints/track', [
            'complaint_number' => $complaint->complaint_number,
            'reporter_email' => 'warga@example.com',
        ])->assertOk()->assertSee('Selesai')->assertSee('Petugas loket sudah ditambah pada jam sibuk.');
    }

    public function test_dashboards_show_each_role_its_own_recap(): void
    {
        Ticket::factory()->create(['status' => 'submitted', 'mode' => 'offline']);
        Ticket::factory()->create(['status' => 'completed', 'mode' => 'online']);

        $this->actingAs($this->staff('back_office'))->get('/cp')->assertOk()->assertSee('Dashboard Back Office');
        Livewire::test(Widgets\BackOffice\BackOfficeStats::class)->assertSee('Tugas saya')->assertSee('Belum ditugaskan');

        $this->actingAs($this->staff('front_desk'))->get('/cp')->assertOk()->assertSee('Dashboard Loket');
        Livewire::test(Widgets\FrontDesk\FrontDeskStats::class)->assertSee('Tamu hari ini')->assertSee('Siap diambil');

        $this->actingAs($this->staff('kepala_sekolah'))->get('/cp')->assertOk()->assertSee('Dashboard Pimpinan');
        Livewire::test(Widgets\Performance\ServicePerformance::class)->assertSee('Kinerja per layanan');

        $this->actingAs($this->staff('supervisor'))->get('/cp')->assertOk()->assertSee('Dashboard Pengawasan');
        Livewire::test(Widgets\Supervision\SupervisionStats::class)->assertSee('IKM tahun ini');

        $this->actingAs($this->staff('admin'))->get('/admin')->assertRedirect('/cp');
    }

    private function expectExceptionRefused(callable $callback): void
    {
        try {
            $callback();
        } catch (TicketActionException) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->fail('The action should have been refused.');
    }
}
