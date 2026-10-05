<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Tabel permohonan: indexes, safe foreign keys and value checks. */
class TicketSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_columns_are_indexed(): void
    {
        $indexes = collect(Schema::getIndexes('tickets'))->pluck('name');

        foreach (['tickets_status_created_at_index', 'tickets_service_id_index', 'tickets_assigned_to_id_index',
            'tickets_created_at_index', 'tickets_estimated_completion_date_index', 'tickets_disposition_recipients_index'] as $index) {
            $this->assertContains($index, $indexes);
        }
    }

    public function test_removing_an_officer_keeps_their_requests(): void
    {
        $officer = User::factory()->create();
        $ticket = Ticket::factory()->create(['assigned_to_id' => $officer->id, 'created_by' => $officer->id]);

        $officer->forceDelete();

        $ticket = Ticket::find($ticket->id);
        $this->assertNotNull($ticket);
        $this->assertNull($ticket->assigned_to_id);
        $this->assertNull($ticket->created_by);
    }

    public function test_applicant_or_service_with_requests_cannot_be_hard_deleted(): void
    {
        $ticket = Ticket::factory()->create();

        foreach ([fn () => $ticket->user->forceDelete(), fn () => Service::find($ticket->service_id)->forceDelete()] as $delete) {
            try {
                DB::transaction($delete);
                $this->fail('Hard delete should be refused.');
            } catch (QueryException $e) {
                $this->assertSame('23001', $e->getCode()); // restrict_violation
            }
        }
        $this->assertNotNull(Ticket::find($ticket->id));
    }

    /** @dataProvider invalidValues */
    public function test_unknown_values_are_rejected(string $column, string $value): void
    {
        $ticket = Ticket::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('tickets')->where('id', $ticket->id)->update([$column => $value]);
    }

    public static function invalidValues(): array
    {
        return [
            'status' => ['status', 'selesai'],
            'mode' => ['mode', 'pos'],
            'priority' => ['priority', 'top'],
            'approval_status' => ['approval_status', 'maybe'],
            'incoming_category' => ['incoming_category', 'lainnya'],
        ];
    }
}
