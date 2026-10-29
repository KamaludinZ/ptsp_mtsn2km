<?php

namespace Tests\Feature;

use App\Exports\SuratKeputusanExport;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** An SK asked for from a ticket is found, and downloaded, by the ticket number. */
class RegisterSkTicketSearchTest extends TestCase
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
        $this->actingAs($this->officer);

        Livewire::withQueryParams(['tiket' => $this->ticket->id])->test(RegisterSkNumber::class)
            ->fillForm(['jenis_sk' => Persuratan::JENIS_SK[1], 'perihal' => 'Penugasan guru pendamping'])
            ->call('submit')
            ->assertHasNoFormErrors();
        $this->sk = SuratKeputusan::where('perihal', 'Penugasan guru pendamping')->sole();
    }

    public function test_the_register_finds_the_sk_by_its_ticket_number_and_downloads_just_that(): void
    {
        Excel::fake();

        Livewire::test(ListSuratKeputusan::class)
            ->searchTable($this->ticket->ticket_number)
            ->assertCanSeeTableRecords([$this->sk])
            ->assertCountTableRecords(1)
            ->assertSee('1 SK sesuai filter dan pencarian.')
            ->callAction('excel')
            ->assertNotified('Mengunduh 1 SK');

        Excel::matchByRegex();
        Excel::assertDownloaded('/\.xlsx$/', fn (SuratKeputusanExport $export) => $export->query()->pluck('id')->all() === [$this->sk->id]);
    }

    public function test_the_api_search_finds_it_too(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk?q=' . urlencode($this->ticket->ticket_number))->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.nomor_sk', $this->sk->nomor_sk);
    }

    public function test_the_search_box_takes_the_whole_phrase_like_the_api(): void
    {
        $year = now()->year;

        // SK 2's keterangan has "4 halaman": the number search still finds SK 4 alone.
        Livewire::test(ListSuratKeputusan::class)
            ->searchTable("4 TAHUN {$year}")
            ->assertCanSeeTableRecords(SuratKeputusan::where('tahun', $year)->where('nomor_urut', 4)->get())
            ->assertCountTableRecords(1)
            ->searchTable('Tentang panitia penerimaan')
            ->assertCanSeeTableRecords(SuratKeputusan::where('tahun', $year)->where('nomor_urut', 3)->get())
            ->assertCountTableRecords(1);
    }

    public function test_any_keterangan_is_searchable(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->searchTable('berlaku satu tahun')
            ->assertCanSeeTableRecords(SuratKeputusan::where('keterangan', 'Berlaku satu tahun pelajaran.')->get())
            ->assertCountTableRecords(1);
    }
}
