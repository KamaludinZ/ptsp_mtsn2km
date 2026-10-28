<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** SuratKeputusanService: nomor urut SK per year, never handed out twice. */
class RegisterSkNumberingTest extends TestCase
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

    private function reserve(string $tanggal): SuratKeputusan
    {
        return app(SuratKeputusanService::class)->reserve(Carbon::parse($tanggal), $this->officer, [
            'perihal' => 'Panitia ujian madrasah',
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'nama_peminta' => 'Waka Kurikulum',
        ]);
    }

    private function remove(SuratKeputusan $sk): void
    {
        app(SuratKeputusanService::class)->delete($sk, User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail(), 'Salah input data');
    }

    private function lastIssued(int $year): ?int
    {
        return DB::table('surat_keputusan_sequences')->where('tahun', $year)->value('last_number');
    }

    public function test_numbers_follow_on_within_a_year_and_restart_each_year(): void
    {
        $year = now()->year;

        $this->assertSame(7, $this->reserve("{$year}-06-01")->nomor_urut);
        $this->assertSame(8, $this->reserve("{$year}-06-02")->nomor_urut);
        $this->assertSame('1 TAHUN ' . ($year + 1), $this->reserve(($year + 1) . '-01-02')->nomor_sk);
        $this->assertSame(9, $this->reserve("{$year}-06-03")->nomor_urut);

        $this->assertSame(9, $this->lastIssued($year));
        $this->assertSame(1, $this->lastIssued($year + 1));
    }

    public function test_the_number_of_a_deleted_last_sk_is_not_given_again(): void
    {
        $year = now()->year;
        $last = $this->reserve("{$year}-06-01");
        $this->remove($last);

        $this->assertSame(6, (int) SuratKeputusan::where('tahun', $year)->max('nomor_urut'));
        $this->assertSame(8, SuratKeputusanService::nextNumber($year));
        $this->assertSame("8 TAHUN {$year}", SuratKeputusanService::yearSummary($year)['berikutnya']);
        $this->assertSame(8, $this->reserve("{$year}-06-02")->nomor_urut);
        $this->assertFalse(SuratKeputusan::where('nomor_sk', "7 TAHUN {$year}")->exists());
    }

    public function test_a_gap_left_by_a_deleted_sk_stays_a_gap(): void
    {
        $year = now()->year;
        $this->remove(SuratKeputusan::where('tahun', $year)->where('nomor_urut', 3)->firstOrFail());

        $this->assertSame(7, $this->reserve("{$year}-06-01")->nomor_urut);
        $this->assertFalse(SuratKeputusan::where('tahun', $year)->where('nomor_urut', 3)->exists());
    }

    public function test_an_sk_recorded_outside_the_service_moves_the_numbers_on(): void
    {
        $year = now()->year;
        SuratKeputusan::create([
            'tahun' => $year, 'nomor_urut' => 50, 'nomor_sk' => SuratKeputusan::composeNumber(50, $year), 'tanggal_surat' => "{$year}-05-01",
            'perihal' => 'SK impor', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Kepala Tata Usaha',
        ]);

        $this->assertSame(51, $this->reserve("{$year}-06-01")->nomor_urut);
        $this->assertSame(51, $this->lastIssued($year));
    }

    public function test_a_year_stops_at_9999_even_when_that_sk_was_deleted(): void
    {
        $year = now()->year;
        SuratKeputusan::create([
            'tahun' => $year, 'nomor_urut' => 9998, 'nomor_sk' => SuratKeputusan::composeNumber(9998, $year), 'tanggal_surat' => "{$year}-05-01",
            'perihal' => 'SK uji', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Kepala Tata Usaha',
        ]);
        $this->remove($this->reserve("{$year}-06-01"));

        $this->assertNull(SuratKeputusanService::nextNumber($year));
        $this->expectException(TicketActionException::class);
        $this->expectExceptionMessage("Nomor SK tahun {$year} sudah mencapai 9999.");

        $this->reserve("{$year}-06-02");
    }

    public function test_handing_out_a_number_locks_the_year(): void
    {
        $this->reserve(now()->year . '-06-01');

        $locks = DB::selectOne("select count(*) as n from pg_locks where locktype = 'advisory' and pid = pg_backend_pid()")->n;
        $this->assertGreaterThan(0, $locks);
    }

    public function test_migration_starts_each_year_after_its_recorded_sk(): void
    {
        $migration = require database_path('migrations/2026_10_27_111000_create_surat_keputusan_sequences_table.php');
        $year = now()->year;
        SuratKeputusan::create([
            'tahun' => $year - 1, 'nomor_urut' => 120, 'nomor_sk' => SuratKeputusan::composeNumber(120, $year - 1), 'tanggal_surat' => ($year - 1) . '-12-01',
            'perihal' => 'SK lama', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Kepala Tata Usaha',
        ]);

        $migration->down();
        $migration->up();

        $this->assertSame(6, $this->lastIssued($year));
        $this->assertSame(120, $this->lastIssued($year - 1));
        $this->assertSame(121, SuratKeputusanService::nextNumber($year - 1));
    }
}
