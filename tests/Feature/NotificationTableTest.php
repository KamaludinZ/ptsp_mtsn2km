<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Tabel notifikasi: indexes and clean-up. */
class NotificationTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_has_the_lookup_indexes(): void
    {
        $indexes = collect(Schema::getIndexes('notifications'))->pluck('name');

        $this->assertContains('notifications_created_at_index', $indexes);
        $this->assertContains('notifications_ticket_number_index', $indexes);
        $this->assertContains('notifications_notifiable_type_notifiable_id_read_at_index', $indexes);
    }

    public function test_notifications_can_be_found_by_ticket_number(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        TicketNotification::send($user, $ticket, 'Status berubah');

        $this->assertSame(1, DatabaseNotification::whereRaw("data->'viewData'->>'ticket_number' = ?", [$ticket->ticket_number])->count());
    }

    public function test_old_notifications_are_pruned_nightly(): void
    {
        $user = User::factory()->create();
        $make = fn (int $daysAgo, ?int $readDaysAgo) => $user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'x', 'data' => ['title' => "{$daysAgo}"],
            'read_at' => $readDaysAgo === null ? null : now()->subDays($readDaysAgo),
            'created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo),
        ]);
        $make(10, 5);      // recent, read: kept
        $make(100, 95);    // read long ago: pruned
        $make(100, null);  // old but unread: kept
        $make(200, null);  // very old: pruned

        $this->artisan('notifications:prune')->assertSuccessful()->expectsOutput('1 notifikasi dibaca dan 1 notifikasi lama dihapus.');

        $this->assertEqualsCanonicalizing(['10', '100'], $user->notifications()->get()->pluck('data.title')->all());
        $this->assertTrue(collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->contains(fn ($e) => $e->description === 'notifications-prune'));
    }

    public function test_notifications_link_to_their_request(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        TicketNotification::send($user, $ticket, 'Status berubah', 'Kini diproses.');

        $note = $user->notifications()->sole();
        $this->assertInstanceOf(\App\Models\Notification::class, $note);
        $this->assertSame($ticket->id, $note->ticket_id);
        $this->assertTrue($note->ticket->is($ticket));
        $this->assertSame(['Status berubah', 'Kini diproses.', $ticket->ticket_number], [$note->title(), $note->body(), $note->ticketNumber()]);
        $this->assertSame(1, $ticket->notifications()->count());
        $this->assertSame(1, \App\Models\Notification::forTicket($ticket)->count());
        $this->assertSame(1, $user->unreadNotifications()->count());

        // Removing the request keeps the notification, without the link.
        \Illuminate\Support\Facades\DB::statement("SET LOCAL app.allow_history_changes = 'on'");
        $ticket->forceDelete();
        $this->assertNull($note->fresh()->ticket_id);
    }

    public function test_existing_notifications_are_linked_by_their_ticket_number(): void
    {
        $migration = require database_path('migrations/2026_10_06_200000_add_ticket_id_to_notifications_table.php');
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $migration->down();
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'legacy', 'notifiable_type' => User::class, 'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Lama', 'ticket_number' => $ticket->ticket_number]), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration->up();

        $this->assertSame($ticket->id, $user->notifications()->sole()->ticket_id);
    }
}
