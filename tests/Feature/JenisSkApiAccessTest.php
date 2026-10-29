<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /api/persuratan/jenis-sk: read with any SK token, change with a Master Jenis SK token, by the active role. */
class JenisSkApiAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::factory()->create(['user_type' => 'pegawai', 'email' => 'tu.jenis@mtsn2malang.sch.id', 'password' => Hash::make('rahasia-tu-123')]);
        $this->officer->assignRole('back_office');
    }

    private function token(array $kemampuan): string
    {
        return $this->postJson('/api/auth/masuk', ['email' => 'tu.jenis@mtsn2malang.sch.id', 'password' => 'rahasia-tu-123', 'kemampuan' => $kemampuan])
            ->assertOk()->assertJsonPath('kemampuan', $kemampuan)->json('token');
    }

    public function test_a_register_token_reads_the_jenis_but_cannot_change_them(): void
    {
        $token = $this->token(['penomoran-sk:tulis']);
        $jenis = PersuratanMaster::where('type', 'jenis_sk')->firstOrFail();

        $this->withToken($token)->getJson('/api/persuratan/jenis-sk')->assertOk();
        $this->withToken($token)->getJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertOk();
        $this->withToken($token)->postJson('/api/persuratan/jenis-sk', ['nama' => 'SK Penghargaan'])->assertForbidden()
            ->assertJsonPath('kemampuan_dibutuhkan', ['jenis-sk:kelola']);
        $this->withToken($token)->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['aktif' => false])->assertForbidden();
        $this->assertTrue($jenis->fresh()->is_active);
    }

    public function test_a_master_token_keeps_the_jenis(): void
    {
        $token = $this->token(['jenis-sk:kelola']);

        $id = $this->withToken($token)->postJson('/api/persuratan/jenis-sk', ['nama' => 'SK Penghargaan'])->assertCreated()->json('id');
        $this->withToken($token)->putJson("/api/persuratan/jenis-sk/{$id}", ['aktif' => false])->assertOk()->assertJsonPath('aktif', false);
        $this->withToken($token)->getJson('/api/persuratan/jenis-sk')->assertOk();
        // The master token is no register token.
        $this->withToken($token)->getJson('/api/penomoran-sk')->assertForbidden();
    }

    public function test_the_jenis_follow_the_active_role(): void
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(['front_desk', 'back_office']);
        $user->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        Sanctum::actingAs($user->fresh(), ['*']);

        $this->getJson('/api/persuratan/jenis-sk')->assertForbidden()
            ->assertJsonPath('peran_aktif', 'front_desk')
            ->assertJsonPath('peran_yang_berwenang', ['back_office']);

        $user->forceFill(['active_role_id' => Role::findByName('back_office', 'web')->id])->save();
        Sanctum::actingAs($user->fresh(), ['*']);
        $this->getJson('/api/persuratan/jenis-sk')->assertOk();
    }

    public function test_the_new_ability_is_offered_at_login(): void
    {
        $this->assertArrayHasKey('jenis-sk:kelola', \App\Http\Controllers\Api\AuthController::TOKEN_ABILITIES);
        $this->postJson('/api/auth/masuk', ['email' => 'tu.jenis@mtsn2malang.sch.id', 'password' => 'rahasia-tu-123', 'kemampuan' => ['jenis-sk:hapus']])
            ->assertJsonValidationErrors(['kemampuan.0' => 'penomoran-sk:baca, penomoran-sk:tulis, jenis-sk:kelola']);
    }
}
