<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Widgets\SkNumberHistory;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Riwayat nomor SK under the register: numbers taken and SK deleted, by whom and why. */
class RegisterSkNumberHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    private function takeNumber(): SuratKeputusan
    {
        $this->actingAs($this->officer);

        return app(SuratKeputusanService::class)->reserve(Carbon::parse(now()->year . '-10-01'), $this->officer, [
            'perihal' => 'Panitia class meeting', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan',
        ]);
    }

    private function deleteSecond(): SuratKeputusan
    {
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        $this->actingAs($this->admin);
        app(SuratKeputusanService::class)->delete($sk, $this->admin, 'Salah catat, SK dibatalkan');

        return $sk;
    }

    public function test_the_history_lists_numbers_taken_and_sk_deleted_newest_first(): void
    {
        $taken = $this->takeNumber();
        $deleted = $this->deleteSecond();
        $this->actingAs($this->officer);

        Livewire::test(SkNumberHistory::class)
            ->assertSee('Riwayat nomor SK')
            ->assertSeeInOrder(['SK dihapus', $deleted->nomor_sk, $this->admin->name, 'Salah catat, SK dibatalkan', 'Nomor diambil', $taken->nomor_sk])
            ->assertSee('Tentang Panitia class meeting')
            ->assertSee($this->officer->name)
            ->assertSee(RoleAccess::roleLabel('back_office'));
    }

    public function test_the_history_filters_by_event_and_year(): void
    {
        $taken = $this->takeNumber();
        $deleted = $this->deleteSecond();
        $this->actingAs($this->officer);

        Livewire::test(SkNumberHistory::class)
            ->filterTable('event', 'deleted')
            ->assertSee($deleted->nomor_sk)
            ->assertDontSee($taken->nomor_sk)
            ->resetTableFilters()
            ->filterTable('tahun', now()->year)
            ->assertCountTableRecords(2)
            ->filterTable('tahun', now()->year - 1)
            ->assertCountTableRecords(0)
            ->assertSee('Belum ada riwayat nomor SK');
    }

    public function test_a_number_taken_in_a_borrowed_account_names_the_administrator(): void
    {
        $this->actingAs($this->admin)
            ->post(route('ganti-akun.mulai', $this->officer), ['alasan' => 'Meninjau register SK petugas']);
        app(SuratKeputusanService::class)->reserve(now(), auth()->user(), [
            'perihal' => 'Tim adiwiyata', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan',
        ]);

        Livewire::test(SkNumberHistory::class)
            ->assertSee($this->officer->name)
            ->assertSee('lewat ganti akun oleh ' . $this->admin->name);
    }

    public function test_the_register_shows_the_history_to_those_who_work_it(): void
    {
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->assertFalse(SkNumberHistory::canView());

        $this->takeNumber();
        $this->get(SuratKeputusanResource::getUrl('index'))->assertOk()->assertSee('Riwayat nomor SK');
    }
}
