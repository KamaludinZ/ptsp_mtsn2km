<?php

namespace Tests\Feature;

use App\Filament\Pages\System\ActivityTrail;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Rekam Jejak Aktivitas: the Register SK trail next to role switches, ganti akun and ticket actions. */
class RegisterSkActivityTrailTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private User $admin;

    private SuratKeputusan $taken;

    private SuratKeputusan $deleted;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $service = app(SuratKeputusanService::class);

        $this->actingAs($this->officer);
        $this->taken = $service->reserve(Carbon::parse(now()->year . '-10-01'), $this->officer, [
            'perihal' => 'Panitia class meeting', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan',
        ]);
        $service->describe($this->taken, ['perihal' => 'Panitia class meeting semester ganjil', 'tanggal_surat' => now()->year . '-10-03']);

        $this->deleted = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        $this->actingAs($this->admin);
        $service->delete($this->deleted, $this->admin, 'Salah catat, SK dibatalkan');
    }

    private function entry(string $event): Activity
    {
        return Activity::inLog('surat_keputusan')->where('event', $event)->latest('id')->firstOrFail();
    }

    public function test_the_trail_has_a_register_sk_kind(): void
    {
        Livewire::test(ActivityTrail::class)
            ->call('setType', 'register_sk')
            ->assertSeeHtml('data-trail-type="register_sk"')
            ->assertDontSeeHtml('data-trail-type="role_switch"')
            ->assertSee('Meminta nomor SK ' . $this->taken->nomor_sk)
            ->assertSee('Mengubah data SK ' . $this->taken->nomor_sk)
            ->assertSee('SK ' . $this->deleted->nomor_sk)
            ->assertSee('sebagai ' . RoleAccess::roleLabel('back_office'))
            ->assertViewHas('counts', fn ($counts) => $counts['register_sk'] === 3);
    }

    public function test_the_detail_shows_what_a_correction_changed(): void
    {
        $year = now()->year;

        Livewire::test(ActivityTrail::class)
            ->call('showDetail', $this->entry('updated')->id)
            ->assertSee('Register SK')
            ->assertSee('Panitia class meeting → Panitia class meeting semester ganjil')
            ->assertSee('1 Okt ' . $year . ' → 3 Okt ' . $year);
    }

    public function test_the_detail_of_a_deletion_keeps_the_sk_and_the_reason(): void
    {
        Livewire::test(ActivityTrail::class)
            ->call('showDetail', $this->entry('deleted')->id)
            ->assertSee($this->deleted->nomor_sk)
            ->assertSee($this->deleted->perihal)
            ->assertSee('Alasan hapus')
            ->assertSee('Salah catat, SK dibatalkan');
    }

    public function test_the_api_lists_the_register_sk_trail(): void
    {
        Sanctum::actingAs($this->admin);

        $rows = $this->getJson('/api/rekam-jejak?jenis=register_sk')->assertOk()->json('data');

        $this->assertSame(['deleted', 'updated', 'created'], array_column($rows, 'kegiatan'));
        $this->assertSame(['register_sk'], array_values(array_unique(array_column($rows, 'jenis'))));
        $this->assertSame([$this->deleted->nomor_sk, null, $this->taken->nomor_sk], array_column($rows, 'nomor_sk'));
    }
}
