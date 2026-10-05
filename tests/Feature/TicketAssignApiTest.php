<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Officer actions through the API: assignment, the service product and the hand-over. */
class TicketAssignApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_officer_assigns_a_responsible_staff_member(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $staff = User::where('email', 'staff2@mtsn2malang.sch.id')->firstOrFail();

        $this->putJson("/api/tiket/{$ticket->ticket_number}/petugas", ['petugas_id' => $staff->id, 'catatan' => 'Mohon segera'])
            ->assertOk()
            ->assertJsonPath('message', "Tiket ditugaskan kepada {$staff->name}.");

        $ticket->refresh();
        $this->assertSame([$staff->id, 'in_process'], [$ticket->assigned_to_id, $ticket->status]);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $ticket->id, 'action' => 'assigned']);
    }

    public function test_only_active_staff_can_be_assigned_and_only_by_workers(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $applicant = tap(User::factory()->create(['user_type' => 'umum']))->assignRole('umum');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->putJson("/api/tiket/{$ticket->ticket_number}/petugas", ['petugas_id' => $applicant->id])->assertUnprocessable()->assertJsonValidationErrors('petugas_id');
        $this->putJson("/api/tiket/{$ticket->ticket_number}/petugas", [])->assertJsonValidationErrors('petugas_id');

        Sanctum::actingAs($applicant);
        $this->putJson("/api/tiket/{$ticket->ticket_number}/petugas", ['petugas_id' => $applicant->id])->assertForbidden();
    }

    public function test_officer_uploads_the_product_and_the_front_desk_hands_it_over(): void
    {
        Storage::fake('local');
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'mode' => 'offline', 'approval_required' => false]);
        $url = "/api/tiket/{$ticket->ticket_number}";

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson("{$url}/hasil", [])->assertJsonValidationErrors('berkas');
        $this->postJson("{$url}/hasil", ['berkas' => UploadedFile::fake()->create('surat-keterangan.pdf', 40, 'application/pdf'), 'keterangan' => 'Siap diambil'])
            ->assertCreated();
        $ticket->refresh();
        $this->assertSame('completed', $ticket->status);
        $this->assertTrue($ticket->ready_for_pickup);
        Storage::disk('local')->assertExists($ticket->output->file_path);
        $this->postJson("{$url}/serah-terima")->assertForbidden();

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson("{$url}/serah-terima")->assertOk();
        $this->assertFalse($ticket->fresh()->ready_for_pickup);
        $this->postJson("{$url}/serah-terima")->assertUnprocessable();
    }
}
