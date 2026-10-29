<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** The Register Penomoran SK migrations roll back cleanly and build the same schema again. */
class RegisterSkMigrationsTest extends TestCase
{
    use RefreshDatabase;

    /** Every migration of the SK register, in the order they run. */
    private const MIGRATIONS = [
        '2026_10_27_100000_add_jenis_sk_to_persuratan_masters',
        '2026_10_27_105000_add_jenis_sk_constraints_to_persuratan_masters',
        '2026_10_27_110000_create_surat_keputusan_table',
        '2026_10_27_110500_add_entry_rules_to_surat_keputusan',
        '2026_10_27_111000_create_surat_keputusan_sequences_table',
        '2026_10_27_112000_add_scan_archive_details_to_surat_keputusan',
        '2026_10_28_100000_add_report_indexes_to_surat_keputusan',
        '2026_10_28_110000_grant_register_sk_permission',
        '2026_10_28_120000_guard_surat_keputusan_register_and_trail',
        '2026_10_28_130000_add_versi_to_surat_keputusan',
        '2026_10_28_140000_add_ticket_to_surat_keputusan',
        '2026_10_28_150000_add_jenis_sk_to_services',
        '2026_10_28_160000_add_kunci_permintaan_to_surat_keputusan',
        '2026_10_28_170000_add_jenis_sk_id_to_surat_keputusan',
        '2026_10_28_180000_add_report_columns_to_surat_keputusan',
        '2026_10_28_190000_add_scan_hash_to_surat_keputusan',
    ];

    private function migration(string $name): object
    {
        return require database_path("migrations/{$name}.php");
    }

    /** Columns, constraints, indexes and triggers the SK register owns. */
    private function schema(): array
    {
        $tables = ['surat_keputusan', 'surat_keputusan_sequences'];

        return [
            'columns' => DB::table('information_schema.columns')->whereIn('table_name', $tables)
                ->orderBy('table_name')->orderBy('column_name')->get(['table_name', 'column_name', 'data_type', 'is_nullable'])->map(fn ($c) => (array) $c)->all(),
            'constraints' => DB::table('pg_constraint')->whereIn('conrelid', fn ($q) => $q->from('pg_class')->whereIn('relname', [...$tables, 'persuratan_masters'])->select('oid'))
                ->orderBy('conname')->selectRaw('conname, pg_get_constraintdef(oid) as def')->pluck('def', 'conname')->all(),
            'indexes' => DB::table('pg_indexes')->whereIn('tablename', [...$tables, 'persuratan_masters', 'activity_log'])
                ->orderBy('indexname')->pluck('indexdef', 'indexname')->all(),
            'triggers' => DB::table('information_schema.triggers')->where('event_object_table', 'surat_keputusan')
                ->orderBy('trigger_name')->orderBy('event_manipulation')->get(['trigger_name', 'event_manipulation'])->map(fn ($t) => (array) $t)->all(),
        ];
    }

    public function test_the_migrations_roll_back_and_build_the_same_schema_again(): void
    {
        $before = $this->schema();
        // The snapshot holds what matters, so comparing it means something.
        $this->assertContains(['trigger_name' => 'surat_keputusan_guard', 'event_manipulation' => 'DELETE'], $before['triggers']);
        $this->assertArrayHasKey('surat_keputusan_berkas_scan_unique', $before['indexes']);
        $this->assertArrayHasKey('surat_keputusan_nomor_sk_check', $before['constraints']);
        $this->assertArrayHasKey('persuratan_masters_jenis_sk_nama_check', $before['constraints']);

        foreach (array_reverse(self::MIGRATIONS) as $name) {
            $this->migration($name)->down();
        }

        $this->assertFalse(Schema::hasTable('surat_keputusan'));
        $this->assertFalse(Schema::hasTable('surat_keputusan_sequences'));
        $this->assertSame(0, DB::table('persuratan_masters')->where('type', 'jenis_sk')->count());
        $this->assertSame([], array_filter(array_keys($this->schema()['indexes']), fn (string $index) => str_contains($index, 'surat_keputusan') || str_contains($index, 'jenis_sk') || str_contains($index, 'sk_dihapus')));

        foreach (self::MIGRATIONS as $name) {
            $this->migration($name)->up();
        }

        $this->assertEquals($before, $this->schema());
    }

    public function test_the_register_works_again_after_a_rebuild(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        foreach (array_reverse(self::MIGRATIONS) as $name) {
            $this->migration($name)->down();
        }
        foreach (self::MIGRATIONS as $name) {
            $this->migration($name)->up();
        }

        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($officer);
        $sk = app(SuratKeputusanService::class)->reserve(Carbon::parse(now()->year . '-05-02'), $officer, [
            'perihal' => 'Panitia class meeting', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan',
        ]);

        $this->assertSame('1 TAHUN ' . now()->year, $sk->nomor_sk);
        $this->assertSame(1, SuratKeputusan::count());
    }
}
