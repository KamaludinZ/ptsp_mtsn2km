<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/penomoran-sk/nomor: status penomoran of a year, every gap in the register accounted for. */
class RegisterSkNumberingStatusTest extends TestCase
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

    public function test_a_register_without_gaps(): void
    {
        $year = now()->year;

        $this->assertSame([
            'tahun' => $year,
            'nomor_terakhir' => "6 TAHUN {$year}",
            'nomor_berikutnya' => "7 TAHUN {$year}",
            'terbit' => 6,
            'sisa' => 9993,
            'celah' => [],
        ], SuratKeputusanService::numberingStatus($year));
    }

    public function test_every_gap_says_whether_it_was_deleted_and_why(): void
    {
        $year = now()->year;
        $this->actingAs($this->admin);
        app(SuratKeputusanService::class)->delete(SuratKeputusan::where('tahun', $year)->where('nomor_urut', 2)->firstOrFail(), $this->admin, 'Salah catat, SK dibatalkan');
        // Number 9 recorded outside the register's flow: 7 and 8 missing without a trail.
        DB::table('surat_keputusan')->insert(['tahun' => $year, 'nomor_urut' => 9, 'nomor_sk' => "9 TAHUN {$year}", 'tanggal_surat' => "{$year}-05-02",
            'perihal' => 'SK impor', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji', 'created_at' => now(), 'updated_at' => now()]);

        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);
        $status = $this->getJson('/api/penomoran-sk/nomor')->assertOk()
            ->assertJsonPath('nomor_terakhir', "9 TAHUN {$year}")
            ->assertJsonPath('terbit', 6)
            ->assertJsonPath('sisa', 9990)
            ->json('celah');

        $this->assertSame([2, 7, 8], array_column($status, 'nomor_urut'));
        $this->assertSame(['dihapus' => true, 'oleh' => $this->admin->name, 'alasan' => 'Salah catat, SK dibatalkan'], array_intersect_key($status[0], array_flip(['dihapus', 'oleh', 'alasan'])));
        $this->assertNotNull($status[0]['pada']);
        $this->assertSame(['nomor_urut' => 7, 'nomor_sk' => "7 TAHUN {$year}", 'dihapus' => false, 'pada' => null, 'oleh' => null, 'alasan' => null], $status[1]);
    }

    public function test_an_empty_year_and_who_may_ask(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $year = now()->year - 1;

        $this->getJson("/api/penomoran-sk/nomor?tahun={$year}")->assertOk()
            ->assertJsonPath('nomor_terakhir', null)
            ->assertJsonPath('nomor_berikutnya', "1 TAHUN {$year}")
            ->assertJsonPath('sisa', 9999)
            ->assertJsonPath('celah', []);
        $this->getJson('/api/penomoran-sk/nomor?tahun=dulu')->assertJsonValidationErrors('tahun');

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson('/api/penomoran-sk/nomor')->assertForbidden();
    }
}
