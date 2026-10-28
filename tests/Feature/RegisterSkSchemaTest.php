<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\ActiveRoles;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Tabel surat_keputusan: the register's rules held by the database itself. */
class RegisterSkSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function row(array $override = []): array
    {
        return [
            'tahun' => 2026,
            'nomor_urut' => 7,
            'nomor_sk' => '7 TAHUN 2026',
            'tanggal_surat' => '2026-05-04',
            'perihal' => 'Panitia penerimaan peserta didik baru',
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'nama_peminta' => 'Waka Kesiswaan',
            'created_at' => now(),
            'updated_at' => now(),
            // A stored scan always says when it came in (surat_keputusan_scan_detail_check).
            ...(filled($override['berkas_scan'] ?? null) ? ['scan_diunggah_pada' => now()] : []),
            ...$override,
        ];
    }

    private function assertRefused(array $override): void
    {
        try {
            DB::transaction(fn () => DB::table('surat_keputusan')->insert($this->row($override)));
        } catch (QueryException) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('Row was accepted: ' . json_encode($override));
    }

    public function test_table_holds_the_sk_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('surat_keputusan', [
            'tahun', 'nomor_urut', 'nomor_sk', 'tanggal_surat', 'perihal', 'jenis_sk', 'nama_peminta',
            'keterangan', 'berkas_scan', 'berkas_scan_nama', 'pembuat_id', 'peran_pembuat', 'created_at', 'updated_at',
        ]));
        $this->assertFalse(Schema::hasColumn('surat_keputusan', 'tujuan_surat'));
    }

    public function test_a_valid_sk_is_stored(): void
    {
        DB::table('surat_keputusan')->insert($this->row());

        $this->assertSame('7 TAHUN 2026', SuratKeputusan::firstOrFail()->nomor_sk);
    }

    public function test_numbers_are_unique_per_year_but_restart_every_year(): void
    {
        DB::table('surat_keputusan')->insert($this->row());
        DB::table('surat_keputusan')->insert($this->row(['tahun' => 2027, 'nomor_sk' => '7 TAHUN 2027', 'tanggal_surat' => '2027-01-02']));

        $this->assertRefused([]);
        $this->assertSame(2, SuratKeputusan::count());
    }

    public function test_sequence_stays_between_1_and_9999(): void
    {
        $this->assertRefused(['nomor_urut' => 0, 'nomor_sk' => '0 TAHUN 2026']);
        $this->assertRefused(['nomor_urut' => 10000, 'nomor_sk' => '10000 TAHUN 2026']);

        DB::table('surat_keputusan')->insert($this->row(['nomor_urut' => 9999, 'nomor_sk' => '9999 TAHUN 2026']));
        $this->assertSame(1, SuratKeputusan::count());
    }

    public function test_number_must_read_n_tahun_yyyy(): void
    {
        $this->assertRefused(['nomor_sk' => '007 TAHUN 2026']);
        $this->assertRefused(['nomor_sk' => '7 tahun 2026']);
        $this->assertRefused(['nomor_sk' => '7/TAHUN/2026']);
        $this->assertRefused(['nomor_sk' => '8 TAHUN 2026']);
    }

    public function test_letter_date_falls_in_the_sk_year(): void
    {
        $this->assertRefused(['tanggal_surat' => '2025-12-31']);
    }

    public function test_describing_fields_may_not_be_blank(): void
    {
        $this->assertRefused(['perihal' => '   ']);
        $this->assertRefused(['jenis_sk' => '']);
        $this->assertRefused(['nama_peminta' => ' ']);
        $this->assertRefused(['perihal' => null]);
    }

    public function test_removing_the_officer_keeps_the_sk(): void
    {
        $officer = User::factory()->create();
        DB::table('surat_keputusan')->insert($this->row(['pembuat_id' => $officer->id, 'peran_pembuat' => 'back_office']));

        $officer->forceDelete();

        $sk = SuratKeputusan::firstOrFail();
        $this->assertNull($sk->pembuat_id);
        $this->assertSame('back_office', $sk->peran_pembuat);
    }

    public function test_search_columns_have_trigram_indexes(): void
    {
        $indexes = collect(DB::select("select indexname from pg_indexes where tablename = 'surat_keputusan'"))->pluck('indexname');

        foreach (['nomor_sk', 'perihal', 'nama_peminta'] as $column) {
            $this->assertContains("idx_surat_keputusan_{$column}_trgm", $indexes);
        }
    }

    public function test_rollback_drops_the_table(): void
    {
        $migration = require database_path('migrations/2026_10_27_110000_create_surat_keputusan_table.php');

        $migration->down();
        $this->assertFalse(Schema::hasTable('surat_keputusan'));

        $migration->up();
        $this->assertTrue(Schema::hasTable('surat_keputusan'));
    }

    public function test_a_scan_is_one_file_in_the_surat_keputusan_directory(): void
    {
        $this->assertRefused(['berkas_scan' => '']);
        $this->assertRefused(['berkas_scan' => 'lampiran/7.pdf']);
        $this->assertRefused(['berkas_scan' => 'surat-keputusan/']);
        $this->assertRefused(['berkas_scan' => 'surat-keputusan/2026/7.pdf']);
        $this->assertRefused(['berkas_scan' => 'surat-keputusan/../.env']);
        $this->assertRefused(['berkas_scan' => '/surat-keputusan/7.pdf']);

        DB::table('surat_keputusan')->insert($this->row(['berkas_scan' => 'surat-keputusan/01JB7S2K.pdf', 'berkas_scan_nama' => 'SK Panitia PPDB.pdf']));
        $this->assertSame('SK Panitia PPDB.pdf', SuratKeputusan::firstOrFail()->scanName());
    }

    public function test_a_scan_name_needs_a_stored_scan(): void
    {
        $this->assertRefused(['berkas_scan_nama' => 'SK Panitia PPDB.pdf']);
    }

    public function test_no_scan_is_stored_as_null_and_clears_the_scan_name(): void
    {
        $sk = SuratKeputusan::create($this->row(['berkas_scan' => '  ']));
        $this->assertNull($sk->fresh()->berkas_scan);

        $sk->update(['berkas_scan' => 'surat-keputusan/7.pdf', 'berkas_scan_nama' => 'SK 7.pdf']);
        $sk->update(['berkas_scan' => null]);

        $this->assertNull($sk->fresh()->berkas_scan);
        $this->assertNull($sk->fresh()->berkas_scan_nama);
        $this->assertSame('menunggu_scan', $sk->fresh()->status());
    }

    public function test_an_entry_records_the_role_the_officer_was_acting_in(): void
    {
        $officer = User::factory()->create(['user_type' => 'pegawai']);
        $officer->assignRole(Role::findOrCreate('admin', 'web'), Role::findOrCreate('back_office', 'web'));
        $this->actingAs($officer = $officer->fresh());
        request()->setUserResolver(fn () => $officer);
        $data = ['perihal' => 'Pembagian tugas mengajar', 'jenis_sk' => 'Pembagian Tugas dan Beban Kerja', 'nama_peminta' => 'Waka Kurikulum'];

        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, 'back_office');
        $first = app(SuratKeputusanService::class)->reserve(now(), $officer, $data);

        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, 'admin');
        $second = app(SuratKeputusanService::class)->reserve(now(), $officer, $data);

        $this->assertSame('back_office', $first->fresh()->peran_pembuat);
        $this->assertSame('admin', $second->fresh()->peran_pembuat);
    }

    public function test_an_entry_keeps_a_given_role_and_has_none_without_an_officer(): void
    {
        $given = SuratKeputusan::create([...$this->row(), 'pembuat_id' => User::factory()->create()->id, 'peran_pembuat' => 'kepala_tu']);
        $anonymous = SuratKeputusan::create($this->row(['nomor_urut' => 8, 'nomor_sk' => '8 TAHUN 2026']));

        $this->assertSame('kepala_tu', $given->fresh()->peran_pembuat);
        $this->assertNull($anonymous->fresh()->peran_pembuat);
    }

    public function test_entry_rules_roll_back_and_reapply(): void
    {
        $migration = require database_path('migrations/2026_10_27_110500_add_entry_rules_to_surat_keputusan.php');

        $migration->down();
        DB::table('surat_keputusan')->insert($this->row(['berkas_scan' => 'lampiran/7.pdf']));
        SuratKeputusan::withDeletesAllowed(fn () => DB::table('surat_keputusan')->delete());

        $migration->up();
        $this->assertRefused(['berkas_scan' => 'lampiran/7.pdf']);
    }
}
