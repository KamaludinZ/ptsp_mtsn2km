<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Ganti akun sementara on the SK register: the borrowed account is recorded, and the administrator is named. */
class RegisterSkImpersonationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function borrowOfficersAccount(): void
    {
        $this->actingAs($this->admin)
            ->post(route('ganti-akun.mulai', $this->officer), ['alasan' => 'Meninjau register SK petugas'])
            ->assertRedirect();
        $this->assertTrue(auth()->user()->is($this->officer));
    }

    private function data(): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'perihal' => 'Panitia class meeting',
            'nama_peminta' => 'Waka Kesiswaan',
        ];
    }

    public function test_the_request_form_warns_that_the_number_goes_to_the_borrowed_account(): void
    {
        $this->borrowOfficersAccount();

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Ganti akun oleh ' . $this->admin->name)
            ->assertSee('Anda sedang memakai akun ini lewat ganti akun sementara. Nomor dicatat atas nama akun ini; Log & Audit mencatat Anda sebagai pelaku sebenarnya.')
            ->assertDontSee('Terisi otomatis dari akun');

        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $sk)
            ->assertSee('Perubahan dicatat atas nama akun ini');
    }

    public function test_rincian_sk_names_the_administrator_who_took_the_number(): void
    {
        $this->borrowOfficersAccount();
        Livewire::test(ListSuratKeputusan::class)->callAction('reserve', $this->data())->assertHasNoActionErrors();
        $sk = SuratKeputusan::where('perihal', 'Panitia class meeting')->sole();

        $this->assertTrue($sk->pembuat->is($this->officer));
        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertSee('Diambil oleh ' . $this->admin->name . ' saat memakai akun ini (ganti akun sementara)');
    }

    public function test_a_correction_in_a_borrowed_account_names_the_administrator(): void
    {
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        $this->borrowOfficersAccount();

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['nama_peminta' => 'Kepala Madrasah'])
            ->assertHasNoTableActionErrors();

        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertSee('lewat ganti akun oleh ' . $this->admin->name)
            ->assertDontSee('Diambil oleh ' . $this->admin->name);
    }

    public function test_an_officer_working_in_their_own_account_sees_no_such_note(): void
    {
        $this->actingAs($this->officer);
        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertDontSee('Ganti akun oleh')
            ->callMountedAction();
        Livewire::test(ListSuratKeputusan::class)->callAction('reserve', $this->data());
        $sk = SuratKeputusan::where('perihal', 'Panitia class meeting')->sole();

        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertDontSee('ganti akun sementara')
            ->assertDontSee('lewat ganti akun');
    }
}
