<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\TicketService;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Riwayat layanan menyimpan peran aktif saat aksi diambil (ticket_logs.acting_role). */
class TicketLogActingRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function actIn(User $user, string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    public function test_ticket_actions_record_the_active_role(): void
    {
        $this->assertTrue(Schema::hasColumn('ticket_logs', 'acting_role'));
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();

        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk']);
        $this->actIn($user->fresh(), 'kepala_tu');

        app(TicketService::class)->decide($ticket->fresh(), false, $user->fresh(), null, 'Berkas belum lengkap.');

        $log = TicketLog::where('ticket_id', $ticket->id)->where('action', 'rejected')->latest('id')->firstOrFail();
        $this->assertSame('kepala_tu', $log->acting_role);
    }

    public function test_single_role_staff_record_their_role_and_applicants_none(): void
    {
        $ticket = Ticket::query()->firstOrFail();
        $officer = User::role('back_office')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();

        $this->actingAs($officer);
        $byOfficer = TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'note', 'performed_by' => $officer->id, 'notes' => 'Catatan petugas']);
        $byApplicant = TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'applicant_note', 'performed_by' => $applicant->id, 'notes' => 'Catatan pemohon']);
        $bySystem = TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'note', 'performed_by' => null, 'notes' => 'Sistem']);

        $this->assertSame('back_office', $byOfficer->acting_role);
        $this->assertNull($byApplicant->acting_role);
        $this->assertNull($bySystem->acting_role);
    }

    public function test_recorded_role_is_part_of_the_immutable_history(): void
    {
        $ticket = Ticket::query()->firstOrFail();
        $officer = User::role('back_office')->firstOrFail();
        $log = TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'note', 'performed_by' => $officer->id, 'notes' => 'x']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('ticket_logs')->where('id', $log->id)->update(['acting_role' => 'admin']);
    }

    public function test_history_api_shows_the_role_to_staff(): void
    {
        $ticket = Ticket::query()->firstOrFail();
        $officer = User::role('back_office')->firstOrFail();
        TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'note', 'performed_by' => $officer->id, 'notes' => 'Dicek ulang', 'acting_role' => 'back_office']);
        Sanctum::actingAs(User::role('admin')->firstOrFail());

        $entries = collect($this->getJson('/api/tiket/' . $ticket->ticket_number . '/riwayat')->assertOk()->json('riwayat') ?? []);
        $this->assertContains('Back Office', $entries->pluck('sebagai')->filter()->all());
    }
}
