<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Tabel layanan PTSP: unique slugs, safe creator key, value checks. */
class ServiceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_slugs_are_readable_and_unique(): void
    {
        $a = Service::factory()->create(['name' => 'Legalisir Ijazah', 'slug' => null]);
        $b = Service::factory()->create(['name' => 'Legalisir Ijazah', 'slug' => null]);
        $a->delete(); // soft-deleted slugs stay reserved
        $c = Service::factory()->create(['name' => 'Legalisir Ijazah', 'slug' => null]);

        $this->assertSame(['legalisir-ijazah', 'legalisir-ijazah-2', 'legalisir-ijazah-3'], [$a->slug, $b->slug, $c->slug]);

        $this->expectException(QueryException::class);
        DB::table('services')->where('id', $c->id)->update(['slug' => $b->slug]);
    }

    public function test_removing_the_creator_keeps_the_service(): void
    {
        $creator = User::factory()->create();
        $service = Service::factory()->create(['created_by' => $creator->id]);

        $creator->forceDelete();

        $this->assertNull($service->fresh()->created_by);
    }

    /** @dataProvider invalidValues */
    public function test_unknown_values_are_rejected(array $values): void
    {
        $service = Service::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('services')->where('id', $service->id)->update($values);
    }

    public static function invalidValues(): array
    {
        return [
            'mode' => [['mode' => 'pos']],
            'fee' => [['fee' => -5]],
            'signature' => [['signature_recommendation' => 'cap']],
        ];
    }

    public function test_existing_services_get_a_unique_slug(): void
    {
        $migration = require database_path('migrations/2026_10_06_180000_harden_services_table.php');
        $migration->down();
        $ids = collect([['Surat Aktif', null], ['Surat Aktif', null], ['Lain', 'lain']])->map(fn ($row) => DB::table('services')->insertGetId([
            'name' => $row[0], 'code' => uniqid(), 'slug' => $row[1], 'mode' => 'online', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]));

        $migration->up();

        $this->assertSame(['surat-aktif', 'surat-aktif-2', 'lain'], DB::table('services')->whereIn('id', $ids)->orderBy('id')->pluck('slug')->all());
    }
}
