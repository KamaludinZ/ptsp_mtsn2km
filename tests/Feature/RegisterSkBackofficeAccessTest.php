<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\SkRegisterRecap;
use App\Filament\Resources\PersuratanMasterResource;
use App\Filament\Resources\SuratKeputusanResource;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Kebijakan akses back office for the whole SK register: every route is
 * guarded, and each role (as its active role) gets exactly the register's rights.
 */
class RegisterSkBackofficeAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Roles that work the register (they open the back office and hold backoffice.sk.register). */
    private const WORKING = ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'tata_usaha', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'penjamin_mutu'];

    private const OUTSIDE = ['front_desk', 'supervisor'];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($role);
        $user->forceFill(['active_role_id' => Role::findByName($role, 'web')->id])->save();

        return $user->fresh();
    }

    private function sk(int $nomorUrut = 1): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->firstOrFail();
    }

    public function test_every_register_route_is_guarded(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())->filter(fn ($route) => str_starts_with((string) $route->getName(), 'api.penomoran-sk.')
            || str_starts_with((string) $route->getName(), 'api.persuratan.jenis-sk.')
            || $route->getName() === 'api.tiket.sk');
        $this->assertGreaterThanOrEqual(25, $routes->count());

        foreach ($routes as $route) {
            $middleware = implode(' ', $route->gatherMiddleware());
            $this->assertStringContainsString('auth:sanctum', $middleware, $route->getName());
            $this->assertMatchesRegularExpression('/\b(ability|abilities):/', $middleware, $route->getName() . ' has no token ability');
            if ($route->getName() !== 'api.penomoran-sk.akses') {
                $this->assertStringContainsString('izin.aktif:backoffice.access', $middleware, $route->getName() . ' does not follow the active role');
            }
        }

        $this->assertContains('can:view,suratKeputusan', Route::getRoutes()->getByName('surat-keputusan.scan')->gatherMiddleware());
    }

    public function test_the_panel_opens_for_the_working_roles_only(): void
    {
        $sk = $this->sk();
        $pages = [
            SuratKeputusanResource::getUrl('index'),
            SuratKeputusanResource::getUrl('registrasi'),
            SuratKeputusanResource::getUrl('view', ['record' => $sk]),
            SkRegisterRecap::getUrl(),
        ];

        foreach (self::WORKING as $role) {
            $this->actingAs($this->staff($role));
            foreach ($pages as $page) {
                $this->get($page)->assertOk();
            }
            $this->get(PersuratanMasterResource::getUrl('index'))->assertOk();
        }

        foreach (self::OUTSIDE as $role) {
            $this->actingAs($this->staff($role));
            foreach ([...$pages, PersuratanMasterResource::getUrl('index')] as $page) {
                $this->assertContains($this->get($page)->status(), [403, 404], "{$role} opened {$page}");
            }
        }
    }

    public function test_the_api_gives_each_role_the_register_s_rights(): void
    {
        $data = ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Panitia lomba', 'jenis_sk' => Persuratan::JENIS_SK[2], 'nama_peminta' => 'Waka Kesiswaan'];

        foreach ([...self::WORKING, ...self::OUTSIDE] as $i => $role) {
            Sanctum::actingAs($this->staff($role), ['*']);
            $works = in_array($role, self::WORKING, true);
            $sk = $this->sk($i % 6 + 1);

            $this->getJson('/api/penomoran-sk')->assertStatus($works ? 200 : 403);
            $this->getJson("/api/penomoran-sk/{$sk->id}")->assertStatus($works ? 200 : 403);
            $this->getJson('/api/penomoran-sk/rekap')->assertStatus($works ? 200 : 403);
            $this->postJson('/api/penomoran-sk', [...$data, 'perihal' => "Panitia lomba {$role}"])->assertStatus($works ? 201 : 403);
            $this->putJson("/api/penomoran-sk/{$sk->id}", ['keterangan' => "Dicek {$role}."])->assertStatus($works ? 200 : 403);
            // Deleting: Administrator only (checked without deleting).
            $this->deleteJson("/api/penomoran-sk/{$sk->id}", [])->assertStatus($role === 'admin' ? 422 : 403);
            // Anyone on the staff may ask what their active role may do.
            $this->getJson('/api/penomoran-sk/akses')->assertOk()->assertJsonPath('wewenang.lihat', $works);
        }
    }

    public function test_applicants_and_guests_stay_out(): void
    {
        $sk = $this->sk();
        $applicant = User::factory()->create(['user_type' => 'guru']);
        $applicant->assignRole('guru');

        $this->getJson('/api/penomoran-sk')->assertUnauthorized();
        $this->get(route('surat-keputusan.scan', $sk))->assertRedirect();

        Sanctum::actingAs($applicant, ['*']);
        $this->getJson('/api/penomoran-sk')->assertForbidden();
        $this->getJson('/api/penomoran-sk/akses')->assertForbidden();
    }
}
