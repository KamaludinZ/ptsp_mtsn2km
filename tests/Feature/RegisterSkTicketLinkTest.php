<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Permohonan terkait: an SK keeps the PTSP ticket it was asked for. */
class RegisterSkTicketLinkTest extends TestCase
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
        $this->actingAs($this->officer);
    }

    private function registerFromTicket(int $ticketId): SuratKeputusan
    {
        Livewire::withQueryParams(['tiket' => $ticketId])->test(RegisterSkNumber::class)
            ->fillForm(['jenis_sk' => Persuratan::JENIS_SK[1], 'perihal' => 'Penugasan guru pendamping', 'nama_peminta' => 'Waka Kurikulum'])
            ->call('submit')
            ->assertHasNoFormErrors();

        return SuratKeputusan::where('perihal', 'Penugasan guru pendamping')->sole();
    }

    public function test_an_sk_asked_for_from_a_ticket_keeps_it(): void
    {
        $sk = $this->registerFromTicket($this->ticket->id);

        $this->assertTrue($sk->ticket->is($this->ticket));
        $this->assertTrue($this->ticket->suratKeputusan()->first()->is($sk));
    }

    public function test_an_unknown_ticket_links_nothing(): void
    {
        $this->assertNull($this->registerFromTicket(999999)->ticket_id);
    }

    public function test_a_correction_cannot_move_the_link_and_a_number_cannot_be_given_one_from_outside(): void
    {
        $sk = $this->registerFromTicket($this->ticket->id);
        $other = Ticket::whereKeyNot($this->ticket->id)->firstOrFail();

        app(SuratKeputusanService::class)->describe($sk, ['ticket_id' => $other->id, 'nama_peminta' => 'Kepala TU']);
        $this->assertSame($this->ticket->id, $sk->fresh()->ticket_id);

        // The API takes no ticket_id: only the registration page links one, after checking the ticket.
        \Laravel\Sanctum\Sanctum::actingAs($this->officer, ['*']);
        $id = $this->postJson('/api/penomoran-sk', ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Panitia lomba', 'jenis_sk' => Persuratan::JENIS_SK[2], 'nama_peminta' => 'Waka Kesiswaan', 'ticket_id' => $other->id])
            ->assertCreated()->json('id');
        $this->assertNull(SuratKeputusan::find($id)->ticket_id);
    }

    public function test_the_sk_keeps_its_ticket_once_the_ticket_is_deleted(): void
    {
        $sk = $this->registerFromTicket($this->ticket->id);

        // Tickets are deleted softly (their history cannot be removed).
        $this->ticket->delete();

        $this->assertTrue($sk->fresh()->ticket->is($this->ticket));
        $this->assertTrue($sk->fresh()->ticket->trashed());
    }

    public function test_sk_registered_before_the_link_are_linked_from_their_keterangan(): void
    {
        $sk = $this->registerFromTicket($this->ticket->id);
        $plain = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
        DB::table('surat_keputusan')->where('id', $sk->id)->update(['ticket_id' => null]);
        // An SK asked for from a ticket of a service-less request.
        DB::table('surat_keputusan')->where('id', $plain->id)->update(['keterangan' => 'Untuk permohonan ' . $this->ticket->ticket_number . '.']);

        $migration = require database_path('migrations/2026_10_28_140000_add_ticket_to_surat_keputusan.php');
        $migration->down();
        $migration->up();

        $this->assertSame($this->ticket->id, $sk->fresh()->ticket_id);
        $this->assertSame($this->ticket->id, $plain->fresh()->ticket_id);
        $this->assertSame(2, SuratKeputusan::whereNotNull('ticket_id')->count());
    }
}
