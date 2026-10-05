<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** /api/pengguna: admin user management. */
class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->admin = User::factory()->create(['user_type' => 'pegawai'])->assignRole('admin');
        Sanctum::actingAs($this->admin);
    }

    public function test_admin_creates_reads_and_lists_users(): void
    {
        $this->postJson('/api/pengguna', [
            'nama' => 'Rina', 'email' => 'Rina@Contoh.id', 'password' => 'Rahasia123', 'kategori' => 'pegawai',
            'whatsapp' => '0812-3456-7890', 'peran' => ['back_office'],
        ])->assertCreated()
            ->assertJsonPath('email', 'rina@contoh.id')
            ->assertJsonPath('whatsapp', '081234567890')
            ->assertJsonPath('peran', ['back_office'])
            ->assertJsonPath('email_terverifikasi', true)
            ->assertJsonPath('aktif', true);

        $rina = User::where('email', 'rina@contoh.id')->firstOrFail();
        $this->assertTrue(Hash::check('Rahasia123', $rina->password));
        User::factory()->create(['name' => 'Budi', 'user_type' => 'umum']);

        $this->getJson("/api/pengguna/{$rina->id}")->assertOk()->assertJsonPath('peran_label', 'Back Office');
        $this->getJson('/api/pengguna?jenis=petugas')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/pengguna?jenis=pemohon')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.nama', 'Budi');
        $this->getJson('/api/pengguna?q=0812345')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/pengguna?peran=back_office')->assertJsonPath('data.0.nama', 'Rina');
    }

    public function test_validation_messages(): void
    {
        $this->postJson('/api/pengguna', ['nama' => 'X', 'email' => strtoupper($this->admin->email), 'password' => 'pendek', 'kategori' => 'tamu', 'whatsapp' => '123', 'peran' => ['raja']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'Email sudah dipakai akun lain.', 'password', 'kategori', 'whatsapp', 'peran.0']);
    }

    public function test_update_changes_only_what_is_sent(): void
    {
        $user = User::factory()->create(['name' => 'Lama', 'user_type' => 'umum']);
        $hash = $user->password;

        $this->patchJson("/api/pengguna/{$user->id}", ['nama' => 'Baru', 'peran' => ['front_desk']])->assertOk()
            ->assertJsonPath('nama', 'Baru')->assertJsonPath('peran', ['front_desk']);
        $this->assertSame([$hash, 'umum'], [$user->fresh()->password, $user->fresh()->user_type]);
    }

    public function test_deactivating_signs_the_account_out_everywhere(): void
    {
        $user = User::factory()->create();
        $user->createToken('hp');
        $user->createToken('laptop');

        $this->patchJson("/api/pengguna/{$user->id}/aktif", ['aktif' => false])->assertOk()
            ->assertJsonPath('token_dicabut', 2)
            ->assertJsonPath('message', 'Akun dinonaktifkan dan dikeluarkan dari semua perangkat.');
        $this->assertFalse($user->fresh()->is_active);
        $this->assertSame(0, $user->tokens()->count());

        $this->patchJson("/api/pengguna/{$user->id}/aktif", ['aktif' => true])->assertOk()->assertJsonPath('aktif', true);
    }

    public function test_admins_cannot_lock_themselves_or_the_office_out(): void
    {
        $this->patchJson("/api/pengguna/{$this->admin->id}/aktif", ['aktif' => false])->assertStatus(422)
            ->assertJsonPath('message', 'Anda tidak dapat menonaktifkan akun sendiri.');
        $this->patchJson("/api/pengguna/{$this->admin->id}", ['peran' => ['back_office']])->assertStatus(422);
        $this->deleteJson("/api/pengguna/{$this->admin->id}")->assertStatus(422);

        // Another admin acting on the only other admin: the last active admin stays.
        $second = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($second);
        $this->patchJson("/api/pengguna/{$this->admin->id}/aktif", ['aktif' => false])->assertOk();
        Sanctum::actingAs($this->admin->fresh());
        $this->assertFalse($this->admin->fresh()->is_active);

        // A deactivated admin can no longer act at all (izin middleware).
        $third = User::factory()->create()->assignRole('admin');
        $third->forceFill(['is_active' => false])->save();
        Sanctum::actingAs($third);
        $this->deleteJson("/api/pengguna/{$second->id}")->assertForbidden();
        $this->assertNotSoftDeleted($second);
    }

    public function test_delete_is_soft_and_admin_only(): void
    {
        $user = User::factory()->create();
        $this->deleteJson("/api/pengguna/{$user->id}")->assertOk();
        $this->assertSoftDeleted($user);

        Sanctum::actingAs(User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office'));
        $this->getJson('/api/pengguna')->assertForbidden();
    }
}
