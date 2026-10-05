<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Services\TicketService;
use App\Support\RoleAccess;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

/** Tabel riwayat status permohonan, filled by the database trigger. */
class TicketStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
    }

    public function test_every_status_a_ticket_enters_is_recorded_with_actor_and_duration(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false, 'created_by' => $this->officer->id]);
        $service = app(TicketService::class);

        $service->changeStatus($ticket, 'verified', 'Berkas lengkap.', $this->officer);

        $rows = $ticket->statusHistories()->get();
        $this->assertSame([[null, 'submitted'], ['submitted', 'verified']], $rows->map(fn ($r) => [$r->from_status, $r->to_status])->all());
        $this->assertSame([$this->officer->id, $this->officer->id], $rows->pluck('changed_by')->all());
        $this->assertNull($rows[0]->previous_duration_seconds);
        $this->assertNotNull($rows[1]->previous_duration_seconds);
        $this->assertSame('Diajukan → Diverifikasi', $rows[1]->label());
    }

    public function test_changes_outside_the_service_layer_are_recorded_too(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        DB::table('tickets')->where('id', $ticket->id)->update(['status' => 'cancelled']);
        DB::table('tickets')->where('id', $ticket->id)->update(['priority' => 'high']); // not a status change

        $this->assertSame(['submitted', 'cancelled'], $ticket->statusHistories()->pluck('to_status')->all());
        $this->assertNull($ticket->statusHistories()->get()->last()->changed_by);
    }

    public function test_logged_in_user_is_the_one_recorded(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $this->actingAs($this->officer);

        $ticket->update(['status' => 'in_process']);

        $this->assertSame($this->officer->id, $ticket->statusHistories()->get()->last()->changed_by);
    }

    public function test_history_cannot_be_changed_or_deleted(): void
    {
        $ticket = Ticket::factory()->create();
        $row = $ticket->statusHistories()->first();

        foreach ([fn () => DB::table('ticket_status_histories')->where('id', $row->id)->update(['to_status' => 'completed']),
            fn () => DB::table('ticket_status_histories')->where('id', $row->id)->delete()] as $change) {
            try {
                DB::transaction($change);
                $this->fail('History change should be refused.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('Riwayat status tidak dapat diubah atau dihapus', $e->getMessage());
            }
        }

        $this->expectException(LogicException::class);
        $row->delete();
    }

    public function test_removing_the_actor_keeps_the_row(): void
    {
        $actor = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $this->actingAs($actor);
        $ticket->update(['status' => 'verified']);
        auth()->logout();

        $actor->forceDelete();

        $this->assertSame([null, 'verified'], [$ticket->statusHistories()->get()->last()->changed_by, $ticket->statusHistories()->get()->last()->to_status]);
    }

    public function test_model_helpers_for_status_and_search(): void
    {
        $siti = User::factory()->create(['name' => 'Siti Aminah']);
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'user_id' => $siti->id, 'created_at' => now()->subDays(3)]);
        $other = Ticket::factory()->create(['status' => 'submitted']);
        $this->actingAs($this->officer);
        $ticket->update(['status' => 'verified']);

        $latest = $ticket->fresh()->latestStatusHistory;
        $this->assertSame('verified', $latest->to_status);
        $this->assertEquals($latest->changed_at, $ticket->fresh()->statusSince());
        $this->assertSame(1, TicketStatusHistory::entered('verified')->count());
        $this->assertNotNull($latest->previousDuration());
        $this->assertNull($ticket->statusHistories()->first()->previousDuration());

        $this->assertSame([$ticket->id], Ticket::search('siti')->pluck('id')->all());
        $this->assertSame([$ticket->id], Ticket::submittedBetween(now()->subDays(4)->toDateString(), now()->subDays(2)->toDateString())->pluck('id')->all());
        $this->assertContains($other->id, Ticket::incomingCategory('disposisi')->pluck('id')->all());
    }

    public function test_every_service_path_records_log_and_status_history_with_the_actor(): void
    {
        $service = app(TicketService::class);
        $leader = User::factory()->create(['user_type' => 'pegawai'])->assignRole('kepala_sekolah');
        $this->assertNull(auth()->id()); // as in a queued job: no logged-in user

        $assigned = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);
        $service->assign($assigned, $this->officer, $this->officer);

        $decided = Ticket::factory()->create(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        $decided->service->update(['approval_roles' => null, 'approval_users' => null]);
        $service->decide($decided, false, $leader, notes: 'Tidak memenuhi syarat.');

        $changed = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);
        $service->changeStatus($changed, 'cancelled', 'Duplikat.', $this->officer);

        foreach ([[$assigned, 'in_process', 'assigned', $this->officer], [$decided, 'rejected', 'rejected', $leader], [$changed, 'cancelled', 'status_changed', $this->officer]] as [$ticket, $status, $action, $actor]) {
            $history = $ticket->statusHistories()->get()->last();
            $this->assertSame([$status, $actor->id], [$history->to_status, $history->changed_by], $action);

            $log = $ticket->logs()->where('to_status', $status)->latest('id')->first();
            $this->assertSame([$action, $actor->id], [$log->action, $log->performed_by], $action);
        }

        $this->assertNull(\App\Models\Ticket::$actingUserId);
    }

    public function test_model_updates_outside_the_service_are_logged_as_well(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $this->actingAs($this->officer);

        $ticket->update(['status' => 'verified']);

        $log = $ticket->logs()->latest('id')->first();
        $this->assertSame(['status_changed', 'submitted', 'verified', 'Status diubah di luar alur layanan.'], [$log->action, $log->from_status, $log->to_status, $log->notes]);
        $this->assertSame($this->officer->id, $ticket->statusHistories()->get()->last()->changed_by);
    }
}
