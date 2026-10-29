<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Riwayat perubahan: one SK's trail in Log & Audit, for the officers who work the register. */
class RegisterSkHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private User $admin;

    private SuratKeputusan $sk;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $service = app(SuratKeputusanService::class);
        $year = now()->year;

        $this->actingAs($this->officer);
        $this->sk = $service->reserve(Carbon::parse("{$year}-10-01"), $this->officer, [
            'perihal' => 'Panitia class meeting', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan',
        ]);
        $this->travel(1)->hour();
        $service->describe($this->sk->fresh(), ['perihal' => 'Panitia class meeting semester ganjil', 'tanggal_surat' => "{$year}-10-03"]);
        $this->travel(1)->hour();
        $this->actingAs($this->admin);
        $service->describe($this->sk->fresh(), ['nama_peminta' => 'Kepala Madrasah', 'keterangan' => 'Disahkan rapat pimpinan.']);
    }

    public function test_the_history_lists_every_entry_oldest_first(): void
    {
        $year = now()->year;
        [$taken, $first, $second] = SuratKeputusanService::history($this->sk);

        $this->assertSame(['created', 'Nomor diambil', $this->officer->name, RoleAccess::roleLabel('back_office')], [$taken['peristiwa'], $taken['label'], $taken['oleh'], $taken['peran']]);
        $this->assertSame([], $taken['perubahan']);

        $this->assertSame([
            ['kolom' => 'perihal', 'label' => 'Perihal', 'lama' => 'Panitia class meeting', 'baru' => 'Panitia class meeting semester ganjil'],
            ['kolom' => 'tanggal_surat', 'label' => 'Tanggal surat', 'lama' => "{$year}-10-01", 'baru' => "{$year}-10-03"],
        ], collect($first['perubahan'])->sortBy('kolom')->values()->all());

        $this->assertSame($this->admin->name, $second['oleh']);
        $this->assertSame(['Keterangan' => [null, 'Disahkan rapat pimpinan.'], 'Nama peminta' => ['Waka Kesiswaan', 'Kepala Madrasah']],
            collect($second['perubahan'])->sortBy('label')->mapWithKeys(fn ($c) => [$c['label'] => [$c['lama'], $c['baru']]])->all());
        $this->assertTrue($taken['pada']->lt($second['pada']));
    }

    public function test_the_api_gives_the_history(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson("/api/penomoran-sk/{$this->sk->id}/riwayat")->assertOk()
            ->assertJsonPath('nomor_sk', $this->sk->nomor_sk)
            ->assertJsonPath('versi', 3)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.peristiwa', 'created')
            ->assertJsonPath('data.2.oleh', $this->admin->name);

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson("/api/penomoran-sk/{$this->sk->id}/riwayat")->assertForbidden();
    }

    public function test_rincian_sk_shows_the_history_newest_first(): void
    {
        $this->actingAs($this->officer)
            ->get(SuratKeputusanResource::getUrl('view', ['record' => $this->sk]))->assertOk()
            ->assertSee('Riwayat perubahan')
            ->assertSeeInOrder(['Data diubah', $this->admin->name, 'Nama peminta:', 'Waka Kesiswaan → Kepala Madrasah', 'Data diubah', 'Perihal:', 'Nomor diambil', $this->officer->name]);
    }

    public function test_an_sk_without_a_trail_says_so(): void
    {
        // Seeded SK were recorded without Log & Audit.
        $seeded = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();

        $this->assertSame([], SuratKeputusanService::history($seeded));
        $this->actingAs($this->officer)->get(SuratKeputusanResource::getUrl('view', ['record' => $seeded]))->assertOk()
            ->assertSee('Belum ada catatan untuk SK ini.');
    }
}
