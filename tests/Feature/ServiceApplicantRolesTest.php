<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** services.user_types_allowed: the applicant roles a service is open to, known roles only. */
class ServiceApplicantRolesTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_10_28_230000_constrain_service_applicant_roles.php';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_a_service_keeps_its_roles_once_and_in_order_and_none_means_everyone(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum', 'siswa', 'siswa']]);
        $this->assertSame(['siswa', 'umum'], $service->fresh()->user_types_allowed);
        $this->assertSame(['siswa', 'umum'], $service->applicantRoles()->pluck('name')->all());

        $service->update(['user_types_allowed' => []]); // nothing ticked in Kelola Layanan
        $this->assertNull($service->fresh()->user_types_allowed);
        $this->assertSame(\App\Support\Applicants::ROLES, $service->fresh()->applicantRoles()->pluck('name')->all());
    }

    public function test_an_unknown_role_is_refused_not_dropped(): void
    {
        foreach ([['vip'], ['siswa', 'Wali Murid']] as $roles) {
            try {
                DB::transaction(fn () => Service::factory()->create(['user_types_allowed' => $roles]));
                $this->fail('Accepted ' . json_encode($roles));
            } catch (QueryException $e) {
                $this->assertStringContainsString('services_user_types_allowed_check', $e->getMessage());
            }
        }

        $service = Service::factory()->create(['user_types_allowed' => ['siswa']]);
        foreach (['[]', '"siswa"', '["siswa", "vip"]'] as $raw) {
            try {
                DB::transaction(fn () => DB::table('services')->where('id', $service->id)->update(['user_types_allowed' => DB::raw("'{$raw}'::jsonb")]));
                $this->fail('Accepted ' . $raw);
            } catch (QueryException $e) {
                $this->assertStringContainsString('services_user_types_allowed_check', $e->getMessage());
            }
        }
    }

    public function test_the_migration_tidies_earlier_values_and_stops_on_an_unknown_role(): void
    {
        $migration = require database_path('migrations/' . self::MIGRATION);
        $migration->down();

        $set = function (string $code, string $raw) {
            $id = Service::factory()->create(['code' => $code])->id;
            DB::table('services')->where('id', $id)->update(['user_types_allowed' => DB::raw("'{$raw}'::jsonb")]);

            return $id;
        };
        $messy = $set('MSY-1', '["Wali_Murid", " siswa ", "siswa", "instansi_perusahaan"]');
        $empty = $set('MSY-2', '[]');
        $nullJson = $set('MSY-3', 'null');
        $migration->up();

        $roles = fn (int $id) => DB::table('services')->where('id', $id)->value('user_types_allowed');
        $this->assertEqualsCanonicalizing(['walimurid', 'siswa', 'instansi'], json_decode($roles($messy), true));
        $this->assertNull($roles($empty));
        $this->assertNull($roles($nullJson));

        $migration->down();
        $set('MSY-4', '["siswa", "tamu kehormatan"]');
        $this->expectExceptionMessage('Peran pemohon tidak dikenal pada layanan');
        $migration->up();
    }
}
