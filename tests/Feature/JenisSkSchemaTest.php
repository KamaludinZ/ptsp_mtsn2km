<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Support\Persuratan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Jenis SK rows in persuratan_masters: seeded by the migration, one name per list ignoring case. */
class JenisSkSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_10_27_100000_add_jenis_sk_to_persuratan_masters.php');
    }

    private function constraints(): object
    {
        return require database_path('migrations/2026_10_27_105000_add_jenis_sk_constraints_to_persuratan_masters.php');
    }

    /** Insert a row inside a savepoint and return the SQLSTATE it was refused with, or null when stored. */
    private function insertState(string $type, string $nama): ?string
    {
        try {
            DB::transaction(fn () => DB::table('persuratan_masters')->insert(['type' => $type, 'nama' => $nama, 'created_at' => now(), 'updated_at' => now()]));
        } catch (QueryException $e) {
            return $e->getCode();
        }

        return null;
    }

    public function test_migration_adds_the_five_starting_jenis_sk(): void
    {
        $rows = PersuratanMaster::where('type', 'jenis_sk')->orderBy('sort')->get();

        $this->assertSame(Persuratan::JENIS_SK, $rows->pluck('nama')->all());
        $this->assertSame([0, 1, 2, 3, 4], $rows->pluck('sort')->all());
        $this->assertTrue($rows->every(fn (PersuratanMaster $row) => $row->is_active && $row->kode === null));
    }

    public function test_migration_and_seeder_can_run_again_without_duplicates(): void
    {
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->update(['is_active' => false]);

        $this->migration()->up();
        $this->seed(\Database\Seeders\PersuratanMasterSeeder::class);

        $this->assertSame(5, PersuratanMaster::where('type', 'jenis_sk')->count());
        $this->assertFalse(PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->value('is_active'));
    }

    public function test_seeder_fills_an_empty_list_in_order_and_restores_a_removed_default(): void
    {
        PersuratanMaster::where('type', 'jenis_sk')->delete();

        $this->seed(\Database\Seeders\PersuratanMasterSeeder::class);

        $this->assertSame(Persuratan::JENIS_SK, PersuratanMaster::ofType('jenis_sk')->pluck('nama')->all());

        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Pembagian Tugas dan Beban Kerja')->delete();
        PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'Kurikulum dan Akademik', 'sort' => 9]);

        $this->seed(\Database\Seeders\PersuratanMasterSeeder::class);

        $this->assertSame(1, PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Pembagian Tugas dan Beban Kerja')->value('sort'));
        $this->assertSame([...Persuratan::JENIS_SK, 'Kurikulum dan Akademik'], PersuratanMaster::ofType('jenis_sk')->pluck('nama')->all());
    }

    public function test_full_database_seed_includes_the_jenis_sk(): void
    {
        $this->seed();

        $this->assertSame(Persuratan::JENIS_SK, Persuratan::options('jenis_sk'));
    }

    public function test_rollback_removes_only_jenis_sk(): void
    {
        $others = PersuratanMaster::where('type', '!=', 'jenis_sk')->count();

        $this->migration()->down();

        $this->assertSame(0, PersuratanMaster::where('type', 'jenis_sk')->count());
        $this->assertSame($others, PersuratanMaster::count());
    }

    public function test_a_jenis_sk_name_is_stored_once(): void
    {
        $this->expectException(QueryException::class);

        DB::table('persuratan_masters')->insert(['type' => 'jenis_sk', 'nama' => Persuratan::JENIS_SK[0], 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_a_jenis_sk_name_differing_only_in_case_or_edge_spaces_is_refused(): void
    {
        $this->assertSame('23505', $this->insertState('jenis_sk', 'KEUANGAN DAN OPERASIONAL'));
        $this->assertSame('23505', $this->insertState('jenis_sk', 'keuangan dan operasional'));
        $this->assertSame('23514', $this->insertState('jenis_sk', ' Kurikulum dan Akademik '));
        $this->assertNull($this->insertState('jenis_sk', 'Kurikulum dan Akademik'));
        $this->assertSame('23505', $this->insertState('jenis_sk', 'kurikulum DAN akademik'));
        $this->assertSame(6, PersuratanMaster::where('type', 'jenis_sk')->count());
    }

    public function test_a_jenis_sk_name_is_never_blank_or_too_short(): void
    {
        $this->assertSame('23514', $this->insertState('jenis_sk', ''));
        $this->assertSame('23514', $this->insertState('jenis_sk', '   '));
        $this->assertSame('23514', $this->insertState('jenis_sk', 'SK'));
        $this->assertNull($this->insertState('jenis_sk', 'Tim'));
    }

    public function test_renaming_a_jenis_sk_onto_another_in_a_different_case_is_refused(): void
    {
        $jenis = PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->firstOrFail();

        try {
            DB::transaction(fn () => $jenis->update(['nama' => 'kepegawaian dan pengangkatan']));
            $this->fail('A rename onto another jenis in a different case was stored.');
        } catch (QueryException $e) {
            $this->assertSame('23505', $e->getCode());
        }

        $jenis->update(['nama' => 'KEUANGAN dan Operasional']);
        $this->assertSame('KEUANGAN dan Operasional', $jenis->fresh()->nama);
    }

    public function test_the_case_rule_applies_only_to_the_jenis_sk_list(): void
    {
        $tembusan = PersuratanMaster::where('type', 'tembusan')->value('nama');

        $this->assertNull($this->insertState('tembusan', mb_strtoupper($tembusan)));
        $this->assertNull($this->insertState('tembusan', 'TU'));
        $this->assertNull($this->insertState('tujuan_naskah', 'keuangan dan operasional'));
    }

    public function test_constraint_migration_rolls_back_and_reapplies(): void
    {
        $this->constraints()->down();

        $this->assertNull($this->insertState('jenis_sk', 'keuangan dan operasional'));
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'keuangan dan operasional')->delete();

        $this->constraints()->up();

        $this->assertSame('23505', $this->insertState('jenis_sk', 'keuangan dan operasional'));
    }
}
