<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** The SK of a ticket: listed from the ticket, filtered and searched by its number in the register. */
class RegisterSkTicketListTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private Ticket $ticket;

    private SuratKeputusan $sk;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->ticket = Ticket::with('service')->whereHas('service')->firstOrFail();

        // Asked for through the API: linked to the ticket, no keterangan naming it.
        Sanctum::actingAs($this->officer, ['*']);
        $id = $this->postJson('/api/penomoran-sk', [
            'tanggal_surat' => now()->toDateString(), 'perihal' => 'Penugasan guru pendamping', 'jenis_sk' => Persuratan::JENIS_SK[1],
            'nama_peminta' => 'Waka Kurikulum', 'tiket' => $this->ticket->id,
        ])->assertCreated()->json('id');
        $this->sk = SuratKeputusan::findOrFail($id);
    }

    public function test_the_register_finds_it_by_the_ticket_number_without_keterangan(): void
    {
        $this->assertNull($this->sk->keterangan);
        $number = $this->ticket->ticket_number;

        $this->getJson('/api/penomoran-sk?q=' . urlencode($number))->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $this->sk->id);
        $this->getJson('/api/penomoran-sk?tiket=' . urlencode($number))->assertOk()->assertJsonPath('total', 1);
        $this->getJson('/api/penomoran-sk?tiket=' . $this->ticket->id)->assertOk()->assertJsonPath('total', 1);
        $this->getJson('/api/penomoran-sk?tiket=TIDAK-ADA')->assertOk()->assertJsonPath('total', 0);
    }

    public function test_the_panel_search_finds_it_too(): void
    {
        // setUp went through Sanctum, which switched the default guard: back to the panel's.
        auth()->shouldUse('web');
        $this->actingAs($this->officer, 'web');

        Livewire::test(ListSuratKeputusan::class)
            ->searchTable($this->ticket->ticket_number)
            ->assertCanSeeTableRecords([$this->sk])
            ->assertCountTableRecords(1);
    }

    public function test_the_ticket_lists_its_sk(): void
    {
        $this->getJson("/api/tiket/{$this->ticket->ticket_number}/sk")->assertOk()
            ->assertJsonPath('tiket', ['id' => $this->ticket->id, 'nomor' => $this->ticket->ticket_number])
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.nomor_sk', $this->sk->nomor_sk)
            ->assertJsonPath('data.0.tiket.layanan', $this->ticket->service->name);

        $other = Ticket::whereKeyNot($this->ticket->id)->firstOrFail();
        $this->getJson("/api/tiket/{$other->ticket_number}/sk")->assertOk()->assertJsonPath('total', 0);

        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);
        $this->getJson("/api/tiket/{$this->ticket->ticket_number}/sk")->assertOk();
        Sanctum::actingAs($this->officer, ['laporan:baca']);
        $this->getJson("/api/tiket/{$this->ticket->ticket_number}/sk")->assertForbidden();
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson("/api/tiket/{$this->ticket->ticket_number}/sk")->assertForbidden();
    }
}
