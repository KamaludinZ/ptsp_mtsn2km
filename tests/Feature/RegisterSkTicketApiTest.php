<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** POST /api/penomoran-sk with tiket: the SK keeps the PTSP ticket it is asked for, and the register filters by it. */
class RegisterSkTicketApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->ticket = Ticket::with('service')->whereHas('service')->firstOrFail();
        Sanctum::actingAs($this->officer, ['*']);
    }

    private function data(array $override = []): array
    {
        return ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Penugasan guru pendamping', 'jenis_sk' => Persuratan::JENIS_SK[1], 'nama_peminta' => 'Waka Kurikulum', ...$override];
    }

    public function test_an_sk_asked_for_through_the_api_keeps_its_ticket(): void
    {
        $id = $this->postJson('/api/penomoran-sk', $this->data(['tiket' => $this->ticket->id]))->assertCreated()
            ->assertJsonPath('tiket', ['id' => $this->ticket->id, 'nomor' => $this->ticket->ticket_number, 'layanan' => $this->ticket->service->name])
            ->json('id');

        $this->assertSame($this->ticket->id, SuratKeputusan::find($id)->ticket_id);
        $this->getJson("/api/penomoran-sk/{$id}")->assertOk()->assertJsonPath('tiket.nomor', $this->ticket->ticket_number);
        $this->postJson('/api/penomoran-sk', $this->data(['perihal' => 'Tanpa tiket']))->assertCreated()->assertJsonPath('tiket', null);
    }

    public function test_the_register_and_its_report_filter_by_ticket(): void
    {
        $this->postJson('/api/penomoran-sk', $this->data(['tiket' => $this->ticket->id]))->assertCreated();

        $this->getJson('/api/penomoran-sk?tiket=' . $this->ticket->id)->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.perihal', 'Penugasan guru pendamping')
            ->assertJsonPath('ringkasan', ['terarsip' => 0, 'menunggu_scan' => 1]);
        $this->getJson('/api/penomoran-sk?tiket=999999')->assertOk()->assertJsonPath('total', 0);
    }

    public function test_an_unknown_deleted_or_hidden_ticket_is_refused_before_a_number_is_taken(): void
    {
        $before = SuratKeputusan::count();

        $this->postJson('/api/penomoran-sk', $this->data(['tiket' => 999999]))->assertJsonValidationErrors(['tiket' => 'Permohonan (tiket) tidak ditemukan.']);

        $deleted = Ticket::whereKeyNot($this->ticket->id)->firstOrFail();
        $deleted->delete();
        $this->postJson('/api/penomoran-sk', $this->data(['tiket' => $deleted->id]))->assertJsonValidationErrors('tiket');

        // An officer who may not work this ticket (TicketPolicy::update).
        Gate::before(fn (User $user, string $ability, array $args) => $ability === 'update' && ($args[0] ?? null) instanceof Ticket ? false : null);
        $this->postJson('/api/penomoran-sk', $this->data(['tiket' => $this->ticket->id]))->assertJsonValidationErrors(['tiket' => 'Anda tidak dapat meminta nomor SK untuk permohonan ini.']);

        $this->assertSame($before, SuratKeputusan::count());
    }
}
