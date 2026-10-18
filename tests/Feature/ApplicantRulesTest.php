<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FrontDeskService;
use App\Support\Applicants;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Aturan pemohon resmi (App\Support\Applicants): daftar putih peran pemohon. */
class ApplicantRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys(RoleAccess::SYSTEM_ROLES) as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_the_seven_applicant_roles_are_the_official_list(): void
    {
        $this->assertSame(['siswa', 'walimurid', 'alumni', 'umum', 'instansi', 'guru', 'pegawai'], Applicants::ROLES);
        // Tipe pengguna lama dan peran pemohon memakai kode yang sama.
        $this->assertEqualsCanonicalizing(array_keys(FrontDeskService::APPLICANT_TYPES), Applicants::ROLES);
        $this->assertSame('Masyarakat Umum', Applicants::options()['umum']);
        // Status civitas di pendaftaran portal juga peran pemohon resmi.
        $this->assertSame([], array_diff(array_keys(\App\Support\CivitasRegistration::USER_TYPES), Applicants::ROLES));
    }

    public function test_api_accepts_exactly_the_applicant_roles_as_categories(): void
    {
        $admin = $this->user(['admin'], 'pegawai');
        \Laravel\Sanctum\Sanctum::actingAs($admin);

        foreach (Applicants::ROLES as $role) {
            $this->getJson('/api/pengguna?kategori=' . $role)->assertOk();
        }
        $this->getJson('/api/pengguna?kategori=back_office')->assertUnprocessable()->assertJsonValidationErrors('kategori');
    }

    public function test_the_applicant_roles_exist_with_portal_access_after_migrating(): void
    {
        foreach (Applicants::ROLES as $role) {
            $this->assertArrayHasKey($role, RoleAccess::SYSTEM_ROLES, $role);
            $this->assertSame(['onlineportal.*'], \Database\Seeders\RolePermissionSeeder::DEFAULTS[$role], $role);
            // Dibuat migrasi (setUp hanya memastikan perannya ada, tanpa izin).
            $this->assertTrue(Role::findByName($role, 'web')->hasPermissionTo('onlineportal.access'), $role);
        }
    }

    public function test_staff_and_leadership_roles_are_not_applicant_roles(): void
    {
        foreach (['kepala_sekolah', 'kepala_tu', 'tata_usaha', 'back_office', 'front_desk', 'waka_humas', 'waka_kesiswaan', 'waka_kurikulum', 'waka_sarpras', 'penjamin_mutu', 'admin', 'supervisor'] as $role) {
            $this->assertContains($role, Applicants::NON_APPLICANT_ROLES);
            $this->assertFalse(Applicants::isApplicantRole($role), $role);
        }

        // Setiap peran sistem tergolong tepat satu: pemohon atau bukan pemohon.
        foreach (array_keys(RoleAccess::SYSTEM_ROLES) as $role) {
            $this->assertNotSame(Applicants::isApplicantRole($role), in_array($role, Applicants::NON_APPLICANT_ROLES, true), $role);
        }
    }

    public function test_an_account_is_an_applicant_when_it_holds_an_applicant_role(): void
    {
        $student = $this->user(['siswa'], 'umum');
        $staff = $this->user(['back_office'], 'pegawai');
        // Guru yang juga waka: tetap pemohon dalam perannya sebagai guru.
        $teacherWaka = $this->user(['waka_humas', 'guru'], 'guru');
        // Akun walk-in lama tanpa peran: tipe usernya yang dipakai.
        $legacy = $this->user([], 'alumni');

        $this->assertTrue(Applicants::isApplicant($student));
        $this->assertSame(['siswa'], Applicants::rolesOf($student));
        $this->assertFalse(Applicants::isApplicant($staff), 'user_type pegawai tidak berlaku bila akun sudah punya peran');
        $this->assertTrue(Applicants::isApplicant($teacherWaka));
        $this->assertSame(['guru'], Applicants::rolesOf($teacherWaka));
        $this->assertTrue(Applicants::isApplicant($legacy));
        $this->assertSame(['alumni'], Applicants::rolesOf($legacy));
        $this->assertFalse(Applicants::isApplicant(null));

        $this->assertEqualsCanonicalizing(
            [$student->id, $teacherWaka->id, $legacy->id],
            Applicants::scope(User::query())->pluck('id')->all(),
        );
        $this->assertSame([$student->id], Applicants::scope(User::query(), 'siswa')->pluck('id')->all());
        $this->assertSame([$legacy->id], Applicants::scope(User::query(), 'alumni')->pluck('id')->all());
        // Tipe di luar daftar putih tidak pernah menghasilkan pemohon.
        $this->assertSame([], Applicants::scope(User::query(), 'back_office')->pluck('id')->all());
    }

    public function test_whatsapp_numbers_match_whatever_their_format(): void
    {
        foreach (['081234567890', '0812-3456-7890', '+62 812 3456 7890', '6281234567890', '81234567890'] as $written) {
            $this->assertSame('081234567890', Applicants::normalizePhone($written), $written);
        }
        $this->assertSame('', Applicants::normalizePhone(' - '));

        $stored = $this->user(['umum'], 'umum');
        $stored->forceFill(['whatsapp_number' => '+62 812-3456-7890'])->save();
        $other = $this->user(['umum'], 'umum');
        $other->forceFill(['whatsapp_number' => '081234567891'])->save();

        $this->assertSame([$stored->id], Applicants::wherePhone(User::query(), '081234567890')->pluck('id')->all());
        $this->assertSame([$stored->id], Applicants::wherePhone(User::query(), '6281234567890')->pluck('id')->all());
        // Pencarian petugas: potongan nomor dan awalan 62 juga ketemu.
        $this->assertContains($stored->id, Applicants::lookup('6281234')->pluck('id')->all());
        $this->assertContains($stored->id, Applicants::lookup('3456-789')->pluck('id')->all());
    }

    public function test_counter_and_portal_link_by_whatsapp_in_any_format(): void
    {
        $front = app(\App\Services\FrontDeskService::class);
        $applicant = $this->user(['umum'], 'umum');
        $applicant->forceFill(['whatsapp_number' => '0812-3456-7890'])->save();

        $this->assertTrue($applicant->is($front->findApplicant(['applicant_phone' => '+6281234567890'])));

        $walkIn = User::factory()->unverified()->create(['email' => '6281234567899@walkin.local', 'whatsapp_number' => '6281234567899', 'user_type' => 'umum']);
        $this->assertTrue($walkIn->is($front->findClaimableApplicant('baru@contoh.id', '0812 3456 7899')));
    }

    public function test_user_model_exposes_the_rule(): void
    {
        $student = $this->user(['siswa'], 'siswa');
        $staff = $this->user(['front_desk'], 'umum');

        $this->assertTrue($student->isApplicant());
        $this->assertFalse($staff->isApplicant());
        $this->assertSame([$student->id], User::applicants()->pluck('id')->all());
        $this->assertSame([$student->id], User::applicants('siswa')->pluck('id')->all());
        $this->assertSame([], User::applicants('umum')->pluck('id')->all());
    }

    public function test_api_profile_reports_the_applicant_status(): void
    {
        $teacherWaka = $this->user(['waka_humas', 'guru'], 'guru');
        $staff = $this->user(['front_desk'], 'umum');

        \Laravel\Sanctum\Sanctum::actingAs($teacherWaka);
        $this->getJson('/api/auth/saya')->assertOk()->assertJsonPath('petugas', true)->assertJsonPath('pemohon', true);

        \Laravel\Sanctum\Sanctum::actingAs($staff);
        $this->getJson('/api/auth/saya')->assertOk()->assertJsonPath('petugas', true)->assertJsonPath('pemohon', false);
    }

    public function test_api_applicant_filter_follows_the_rule(): void
    {
        $admin = $this->user(['admin'], 'pegawai');
        $teacherWaka = $this->user(['waka_kurikulum', 'guru'], 'guru');
        $staff = $this->user(['tata_usaha'], 'pegawai');
        $legacy = $this->user([], 'umum');
        // Akun tanpa peran pemohon maupun peran staf (mis. peran lain buatan admin) bukan pemohon.
        Role::findOrCreate('tamu_khusus');
        $other = $this->user(['tamu_khusus'], 'umum');

        \Laravel\Sanctum\Sanctum::actingAs($admin);
        $ids = collect($this->getJson('/api/pengguna?jenis=pemohon')->assertOk()->json('data'))->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$teacherWaka->id, $legacy->id], $ids);
        $this->assertNotContains($staff->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    private function user(array $roles, string $type): User
    {
        $user = User::factory()->create(['user_type' => $type]);
        $user->syncRoles($roles);

        return $user->fresh();
    }
}
