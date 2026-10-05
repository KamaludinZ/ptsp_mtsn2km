<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Pengaturan aplikasi: table rules, who changed what, and caching. */
class AppSettingSchemaTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider invalidValues */
    public function test_invalid_rows_are_rejected(array $values): void
    {
        $this->expectException(QueryException::class);
        DB::table('app_settings')->insert($values + ['key' => 'contoh', 'type' => 'text', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()]);
    }

    public static function invalidValues(): array
    {
        return [
            'unknown type' => [['type' => 'json']],
            'key with spaces' => [['key' => 'Nama Aplikasi']],
            'upper-case key' => [['key' => 'APP_NAME']],
        ];
    }

    public function test_keys_are_normalised_and_the_editor_is_recorded(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        AppSetting::set(' Office_Phone ', '0341-123');
        $setting = AppSetting::where('key', 'office_phone')->firstOrFail();
        $this->assertSame($admin->id, $setting->updated_by);
        $this->assertTrue($setting->updatedBy->is($admin));

        $admin->forceDelete();
        $this->assertNull($setting->fresh()->updated_by);
        $this->assertSame('0341-123', $setting->fresh()->value);
    }

    public function test_reading_uses_one_cached_copy_and_saving_refreshes_it(): void
    {
        AppSetting::set('app_name', 'PTSP Lama');
        AppSetting::get('app_name');

        DB::enableQueryLog();
        $this->assertSame('PTSP Lama', AppSetting::get('app_name'));
        $this->assertSame('a', AppSetting::get('tidak_ada', 'a'));
        $this->assertSame('b', AppSetting::get('tidak_ada', 'b')); // each caller's own default
        $this->assertNull(AppSetting::get('tidak_ada'));
        $this->assertCount(0, DB::getQueryLog());

        AppSetting::set('app_name', 'PTSP Baru');
        $this->assertSame('PTSP Baru', AppSetting::get('app_name'));
    }

    public function test_saving_a_setting_keeps_the_rest_of_the_cache(): void
    {
        Cache::put('login_lockout_test', 3, 600);

        AppSetting::set('app_name', 'PTSP');
        AppSetting::where('key', 'app_name')->first()->delete();
        AppSetting::clearCache();

        $this->assertSame(3, Cache::get('login_lockout_test'));
    }

    public function test_existing_rows_are_normalised(): void
    {
        $migration = require database_path('migrations/2026_10_06_260000_harden_app_settings_table.php');
        $migration->down();

        DB::table('app_settings')->insert([
            ['key' => 'Old_Key', 'value' => 'x', 'type' => 'json', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $migration->up();

        $this->assertDatabaseHas('app_settings', ['key' => 'old_key', 'type' => 'text']);
        $this->assertTrue(Schema::hasColumn('app_settings', 'updated_by'));
    }
}
