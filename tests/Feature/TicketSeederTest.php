<?php

namespace Tests\Feature;

use App\Models\DispositionLog;
use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Services\NotificationDispatcher;
use App\Support\IncomingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Contoh permohonan: demo requests in every stage, with consistent history. */
class TicketSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_requests_cover_every_stage_with_history(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-10-15 13:00'));

        $this->seed();

        $this->assertSame('2026-10-15 13:00', now()->format('Y-m-d H:i')); // the seeder restores the clock
        $this->assertFalse(NotificationDispatcher::$muted);
        Mail::assertNothingSent();
        Mail::assertNothingQueued();

        $statuses = Ticket::pluck('status')->countBy();
        foreach (['submitted', 'verified', 'approved', 'in_process', 'completed', 'rejected', 'cancelled'] as $status) {
            $this->assertGreaterThan(0, $statuses[$status] ?? 0, $status);
        }

        $categories = Ticket::where('approval_status', 'approved')->get()->map(fn (Ticket $t) => IncomingCategory::of($t))->unique();
        $this->assertEqualsCanonicalizing(['disposisi', 'tembusan', 'koordinasi', 'arahan'], $categories->values()->all());

        $this->assertGreaterThan(0, Ticket::overdue()->count());
        $this->assertGreaterThan(0, Ticket::where('status', 'completed')->where('ready_for_pickup', true)->count());
        $this->assertGreaterThan(0, DispositionLog::count());
        $this->assertGreaterThan(0, Ticket::where('created_at', '<', '2026-10-01')->count()); // previous month for the report

        // Every request: one status history row per status entered, the last one matching its status.
        foreach (Ticket::with('statusHistories')->get() as $ticket) {
            $this->assertSame($ticket->status, $ticket->statusHistories->last()->to_status, $ticket->ticket_number);
            $this->assertSame($ticket->logs()->where('action', 'created')->count(), 1);
        }
        $this->assertSame(0, TicketStatusHistory::where('changed_at', '>', now())->count());
    }
}
