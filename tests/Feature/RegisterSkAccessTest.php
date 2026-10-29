<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RegisterSkAccess;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Hak akses Register Penomoran SK: by the active role, deleting for admin only. */
class RegisterSkAccessTest extends TestCase
{
    use RefreshDatabase;

    private const WORK = ['lihat' => true, 'minta_nomor' => true, 'ubah' => true, 'hapus' => false];

    private const ALL = ['lihat' => true, 'minta_nomor' => true, 'ubah' => true, 'hapus' => true];

    private const NONE = ['lihat' => false, 'minta_nomor' => false, 'ubah' => false, 'hapus' => false];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        RoleAccess::sync();
    }

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        foreach ($roles as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user->fresh();
    }

    /** Act in a role the way the middleware sets it for a request. */
    private function actIn(User $user, ?string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    public static function roles(): array
    {
        return [
            'admin' => ['admin', self::ALL],
            'kepala_sekolah' => ['kepala_sekolah', self::WORK],
            'kepala_tu' => ['kepala_tu', self::WORK],
            'back_office' => ['back_office', self::WORK],
            'tata_usaha' => ['tata_usaha', self::WORK],
            'waka_kurikulum' => ['waka_kurikulum', self::WORK],
            'waka_kesiswaan' => ['waka_kesiswaan', self::WORK],
            'penjamin_mutu' => ['penjamin_mutu', self::WORK],
            'front_desk' => ['front_desk', self::NONE],
            'supervisor' => ['supervisor', self::NONE],
        ];
    }

    /** @dataProvider roles */
    public function test_each_role_gets_its_register_abilities(string $role, array $expected): void
    {
        $user = $this->staff($role);
        $this->actIn($user, $role);
        $sk = SuratKeputusan::firstOrFail();

        $this->assertSame($expected, RegisterSkAccess::abilities($user));
        $this->assertSame($expected['lihat'], Gate::forUser($user)->allows('viewAny', SuratKeputusan::class));
        $this->assertSame($expected['minta_nomor'], Gate::forUser($user)->allows('create', SuratKeputusan::class));
        $this->assertSame($expected['ubah'], Gate::forUser($user)->allows('update', $sk));
        $this->assertSame($expected['hapus'], Gate::forUser($user)->allows('delete', $sk));
        $this->assertFalse(Gate::forUser($user)->allows('deleteAny', SuratKeputusan::class));
    }

    public function test_working_roles_are_the_back_office_roles(): void
    {
        $roles = RegisterSkAccess::workingRoles();

        foreach (['admin', 'kepala_sekolah', 'kepala_tu', 'back_office'] as $role) {
            $this->assertContains($role, $roles);
        }
        $this->assertNotContains('front_desk', $roles);
        $this->assertNotContains('supervisor', $roles);
    }

    public function test_abilities_follow_the_active_role_not_every_role_held(): void
    {
        $user = $this->staff('front_desk', 'back_office');

        $this->actIn($user, 'front_desk');
        $this->assertSame(self::NONE, RegisterSkAccess::abilities($user));
        $this->assertFalse(SuratKeputusanResource::canViewAny());

        $this->actIn($user, 'back_office');
        $this->assertSame(self::WORK, RegisterSkAccess::abilities($user));
        $this->assertTrue(SuratKeputusanResource::canViewAny());

        $admin = $this->staff('admin', 'back_office');
        $this->actIn($admin, 'back_office');
        $this->assertFalse(RegisterSkAccess::canDelete($admin));
        $this->actIn($admin, 'admin');
        $this->assertTrue(RegisterSkAccess::canDelete($admin));
    }

    public function test_deactivated_accounts_and_applicants_get_nothing(): void
    {
        $admin = $this->staff('admin');
        $admin->forceFill(['is_active' => false])->save();
        $this->actIn($admin, 'admin');
        $this->assertSame(self::NONE, RegisterSkAccess::abilities($admin));

        $applicant = User::factory()->create(['user_type' => 'umum']);
        $this->actIn($applicant, null);
        $this->assertSame(self::NONE, RegisterSkAccess::abilities($applicant));
    }

    public function test_access_endpoint_reports_the_active_role_and_its_abilities(): void
    {
        $user = $this->staff('front_desk', 'back_office');
        $user->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        Sanctum::actingAs($user->fresh(), ['*']);

        $this->getJson('/api/penomoran-sk/akses')->assertOk()
            ->assertJsonPath('peran_aktif.nama', 'front_desk')
            ->assertJsonPath('wewenang', self::NONE);
        $this->getJson('/api/penomoran-sk')->assertForbidden();

        $this->putJson('/api/peran-aktif', ['peran' => 'back_office'])->assertOk();
        $this->getJson('/api/penomoran-sk/akses')->assertOk()
            ->assertJsonPath('peran_aktif.nama', 'back_office')
            ->assertJsonPath('wewenang', self::WORK);
        $this->getJson('/api/penomoran-sk')->assertOk();
    }

    public function test_access_endpoint_is_for_staff_only(): void
    {
        $this->getJson('/api/penomoran-sk/akses')->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(['user_type' => 'umum']), ['*']);
        $this->getJson('/api/penomoran-sk/akses')->assertForbidden();
    }
}
