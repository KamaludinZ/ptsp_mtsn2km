<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Filament\Resources\TicketResource;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** SuratKeputusanPolicy::createForTicket: an SK number for a ticket, by who may ask for numbers and work that ticket. */
class RegisterSkTicketPolicyTest extends TestCase
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
    }

    private function allows(User $user, Ticket $ticket): bool
    {
        return Gate::forUser($user)->allows('createForTicket', [SuratKeputusan::class, $ticket]);
    }

    public function test_who_may_ask_an_sk_number_for_a_ticket(): void
    {
        $this->actingAs($this->officer);
        $this->assertTrue($this->allows($this->officer, $this->ticket));

        $frontDesk = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($frontDesk);
        $this->assertFalse($this->allows($frontDesk, $this->ticket));

        $multi = User::factory()->create(['user_type' => 'pegawai']);
        $multi->assignRole(['front_desk', 'back_office']);
        $multi->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        $this->actingAs($multi = $multi->fresh());
        $this->assertFalse($this->allows($multi, $this->ticket));

        $this->actingAs($this->officer);
        $this->ticket->delete();
        $this->assertFalse($this->allows($this->officer, $this->ticket->fresh()));
    }

    public function test_the_registration_page_ignores_a_ticket_the_officer_may_not_work(): void
    {
        $this->actingAs($this->officer);
        Gate::before(fn (User $user, string $ability, array $args) => $ability === 'update' && ($args[0] ?? null) instanceof Ticket ? false : null);

        Livewire::withQueryParams(['tiket' => $this->ticket->id])->test(RegisterSkNumber::class)
            ->assertDontSee('Permohonan terkait')
            ->assertFormSet(['nama_peminta' => null, 'keterangan' => null])
            ->fillForm(['tanggal_surat' => now()->toDateString(), 'jenis_sk' => Persuratan::JENIS_SK[1], 'perihal' => 'Penugasan guru pendamping', 'nama_peminta' => 'Waka Kurikulum'])
            ->call('submit');

        $this->assertNull(SuratKeputusan::where('perihal', 'Penugasan guru pendamping')->sole()->ticket_id);
    }

    public function test_rincian_sk_links_the_ticket_for_whoever_may_see_it(): void
    {
        $this->actingAs($this->officer);
        Livewire::withQueryParams(['tiket' => $this->ticket->id])->test(RegisterSkNumber::class)
            ->fillForm(['jenis_sk' => Persuratan::JENIS_SK[1], 'perihal' => 'Penugasan guru pendamping'])
            ->call('submit');
        $sk = SuratKeputusan::where('perihal', 'Penugasan guru pendamping')->sole();

        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertSeeInOrder(['Permohonan terkait', $this->ticket->ticket_number])
            ->assertSee(e(TicketResource::getUrl('view', ['record' => $this->ticket])), false);

        $this->ticket->delete();
        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertSee($this->ticket->ticket_number . ' (dihapus)')
            ->assertDontSee(e(TicketResource::getUrl('view', ['record' => $this->ticket])), false);

        $plain = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $plain]))->assertOk()->assertDontSee('Permohonan terkait');
    }
}
