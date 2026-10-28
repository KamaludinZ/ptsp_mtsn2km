<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Filament\Resources\TicketResource;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Registrasi Nomor SK: Minta Nomor SK as a page of its own. */
class RegisterSkRegistrationPageTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function data(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'perihal' => 'Panitia class meeting',
            'nama_peminta' => 'Waka Kesiswaan',
            ...$override,
        ];
    }

    public function test_the_page_lays_out_the_request_form_with_the_number_to_be_given(): void
    {
        $year = now()->year;

        $this->actingAs($this->officer)
            ->get(SuratKeputusanResource::getUrl('registrasi'))->assertOk()
            ->assertSee('Registrasi Nomor SK')
            ->assertSeeInOrder([SuratKeputusanResource::getBreadcrumb(), 'Registrasi'])
            ->assertSee('wire:click="submitAnother"', false)
            ->assertSeeInOrder(['Data SK', 'Tanggal surat', 'Jenis SK', 'Perihal', 'Nama peminta nomor', 'Scan SK (opsional)', 'Petugas pencatat', 'Nomor yang akan diberikan'])
            ->assertSee($this->officer->name . ' · ' . RoleAccess::roleLabel('back_office'))
            ->assertSee('data-sk-preview="7 TAHUN ' . $year . '"', false)
            ->assertSee('Ambil nomor')
            ->assertSee('Ambil &amp; minta lagi', false)
            ->assertSee('Kembali ke register');
    }

    public function test_taking_a_number_opens_rincian_sk(): void
    {
        $this->actingAs($this->officer);

        $page = Livewire::test(RegisterSkNumber::class)->fillForm($this->data())->call('submit')
            ->assertHasNoFormErrors()
            ->assertNotified('Nomor SK diberikan');

        $sk = SuratKeputusan::where('perihal', 'Panitia class meeting')->sole();
        $page->assertRedirect(SuratKeputusanResource::getUrl('view', ['record' => $sk]));
        $this->assertSame([7, $this->officer->id, 'back_office'], [$sk->nomor_urut, $sk->pembuat_id, $sk->peran_pembuat]);
    }

    public function test_ambil_dan_minta_lagi_keeps_the_date_and_jenis(): void
    {
        $this->actingAs($this->officer);
        $date = now()->startOfMonth()->toDateString();

        $page = Livewire::test(RegisterSkNumber::class)
            ->fillForm($this->data(['tanggal_surat' => $date]))
            ->call('submitAnother')
            ->assertNotified('Nomor SK diberikan')
            ->assertNoRedirect()
            ->assertFormSet(['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'perihal' => null, 'nama_peminta' => null])
            ->assertSee('data-sk-preview="8 TAHUN ' . now()->year . '"', false);

        $this->assertSame($date, \Illuminate\Support\Carbon::parse($page->get('data.tanggal_surat'))->toDateString());

        $this->assertSame(1, SuratKeputusan::where('perihal', 'Panitia class meeting')->count());
    }

    public function test_the_page_checks_the_form_like_the_modal(): void
    {
        $this->actingAs($this->officer);
        $before = SuratKeputusan::count();

        Livewire::test(RegisterSkNumber::class)
            ->fillForm($this->data(['perihal' => '', 'jenis_sk' => 'SK Lainnya']))
            ->call('submit')
            ->assertHasFormErrors(['perihal' => 'required', 'jenis_sk' => 'in']);

        $this->assertSame($before, SuratKeputusan::count());
    }

    public function test_opened_from_a_ticket_the_form_names_the_applicant_and_the_ticket(): void
    {
        $ticket = Ticket::with(['user', 'service'])->whereHas('user')->whereHas('service')->firstOrFail();
        $this->actingAs($this->officer);
        $keterangan = 'Untuk permohonan ' . $ticket->ticket_number . ' (' . $ticket->service->name . ').';

        Livewire::withQueryParams(['tiket' => $ticket->id])->test(RegisterSkNumber::class)
            ->assertSee('Permohonan terkait')
            ->assertSeeHtml('data-sk-ticket="' . $ticket->ticket_number . '"')
            ->assertSee(TicketResource::getUrl('view', ['record' => $ticket]), false)
            ->assertFormSet(['nama_peminta' => $ticket->user->name, 'keterangan' => $keterangan, 'perihal' => null])
            ->fillForm(['jenis_sk' => \App\Support\Persuratan::JENIS_SK[1], 'perihal' => 'Penugasan guru pendamping'])
            ->call('submit')
            ->assertHasNoFormErrors();

        $sk = SuratKeputusan::where('perihal', 'Penugasan guru pendamping')->sole();
        $this->assertSame([$ticket->user->name, $keterangan], [$sk->nama_peminta, $sk->keterangan]);
    }

    public function test_an_unknown_ticket_fills_in_nothing(): void
    {
        $this->actingAs($this->officer);

        Livewire::withQueryParams(['tiket' => 999999])->test(RegisterSkNumber::class)
            ->assertDontSee('Permohonan terkait')
            ->assertFormSet(['nama_peminta' => null, 'keterangan' => null]);
    }

    public function test_proses_tiket_offers_minta_nomor_sk(): void
    {
        $ticket = Ticket::query()->orderBy('id')->firstOrFail();
        $this->actingAs($admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $this->assertTrue($admin->can('update', $ticket));

        $this->get(TicketResource::getUrl('view', ['record' => $ticket]))->assertOk()
            ->assertSee('Minta Nomor SK')
            ->assertSee(e(SuratKeputusanResource::getUrl('registrasi', ['tiket' => $ticket->id])), false);
    }

    public function test_the_register_modal_links_to_the_page_and_only_officers_open_it(): void
    {
        $this->actingAs($this->officer);
        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Buka sebagai halaman')
            ->assertSee(SuratKeputusanResource::getUrl('registrasi'), false);

        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())
            ->get(SuratKeputusanResource::getUrl('registrasi'))
            ->assertForbidden();
    }
}
