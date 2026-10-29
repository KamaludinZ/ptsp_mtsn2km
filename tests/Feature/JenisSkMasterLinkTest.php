<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\JenisSkService;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use App\Support\SkRegisterRecap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** SK keep the Master Jenis SK entry behind their jenis, so reports outlive a rename. */
class JenisSkMasterLinkTest extends TestCase
{
    use RefreshDatabase;

    private PersuratanMaster $panitia;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->panitia = PersuratanMaster::where('type', 'jenis_sk')->where('nama', Persuratan::JENIS_SK[2])->firstOrFail();
    }

    private function rename(): void
    {
        $this->panitia->update(['nama' => 'Kepanitiaan Kegiatan Madrasah']);
    }

    public function test_every_sk_knows_its_master_entry(): void
    {
        $this->assertSame(0, SuratKeputusan::whereNull('jenis_sk_id')->count());
        $this->assertSame([$this->panitia->id], SuratKeputusan::where('jenis_sk', $this->panitia->nama)->distinct()->pluck('jenis_sk_id')->all());

        $sk = app(SuratKeputusanService::class)->reserve(Carbon::parse(now()->toDateString()), auth()->user(), [
            'perihal' => 'Panitia lomba', 'jenis_sk' => mb_strtoupper($this->panitia->nama), 'nama_peminta' => 'Waka Kesiswaan',
        ]);
        $this->assertSame($this->panitia->id, $sk->jenis_sk_id);
        $this->assertTrue($sk->jenisSkMaster->is($this->panitia));
    }

    public function test_a_renamed_jenis_keeps_its_older_sk_in_counts_and_the_recap(): void
    {
        $this->rename();
        $name = 'Kepanitiaan Kegiatan Madrasah';

        // The SK still read as written.
        $this->assertSame(2, SuratKeputusan::where('jenis_sk', Persuratan::JENIS_SK[2])->count());
        $this->assertSame(['semua' => 2, 'tahun' => 2], JenisSkService::usage([$name])[$name]);

        $rows = collect(SkRegisterRecap::build(now()->year)['per_jenis'])->keyBy('jenis');
        $this->assertSame(['jenis' => $name, 'terbit' => 2, 'terarsip' => 0, 'aktif' => true], $rows[$name]);
        $this->assertArrayNotHasKey(Persuratan::JENIS_SK[2], $rows->all());

        // A new SK under the new name counts with them.
        app(SuratKeputusanService::class)->reserve(Carbon::parse(now()->toDateString()), auth()->user(), [
            'perihal' => 'Panitia lomba', 'jenis_sk' => $name, 'nama_peminta' => 'Waka Kesiswaan',
        ]);
        $this->assertSame(3, JenisSkService::usage([$name])[$name]['semua']);
    }

    public function test_an_sk_without_a_master_entry_counts_by_its_own_name(): void
    {
        DB::table('surat_keputusan')->insert([
            'tahun' => now()->year, 'nomor_urut' => 50, 'nomor_sk' => SuratKeputusan::composeNumber(50, now()->year), 'tanggal_surat' => now()->toDateString(),
            'perihal' => 'SK lama', 'jenis_sk' => 'Jenis Lama', 'nama_peminta' => 'Uji', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertSame(['semua' => 1, 'tahun' => 1], JenisSkService::usage(['Jenis Lama'])['Jenis Lama']);
        $this->assertSame(['jenis' => 'Jenis Lama', 'terbit' => 1, 'terarsip' => 0, 'aktif' => false], collect(SkRegisterRecap::build(now()->year)['per_jenis'])->firstWhere('jenis', 'Jenis Lama'));
    }

    public function test_deleting_a_master_entry_keeps_the_sk_and_their_jenis_text(): void
    {
        $this->panitia->delete();

        $this->assertSame(2, SuratKeputusan::where('jenis_sk', Persuratan::JENIS_SK[2])->whereNull('jenis_sk_id')->count());
        $this->assertSame(2, JenisSkService::usage([Persuratan::JENIS_SK[2]])[Persuratan::JENIS_SK[2]]['semua']);
    }
}
