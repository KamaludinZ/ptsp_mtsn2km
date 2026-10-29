<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

/** API tokens limited at login to reading or writing the SK register; the account's role still decides. */
class RegisterSkTokenAbilitiesTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::factory()->create(['user_type' => 'pegawai', 'email' => 'tu.sk@mtsn2malang.sch.id', 'password' => Hash::make('rahasia-tu-123')]);
        $this->officer->assignRole('back_office');
    }

    private function login(array $kemampuan = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/auth/masuk', array_filter([
            'email' => 'tu.sk@mtsn2malang.sch.id', 'password' => 'rahasia-tu-123', 'perangkat' => 'Aplikasi TU', 'kemampuan' => $kemampuan,
        ]));
    }

    private function data(): array
    {
        return ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Panitia class meeting', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan'];
    }

    public function test_a_read_only_token_reads_and_downloads_but_cannot_take_a_number(): void
    {
        $token = $this->login(['penomoran-sk:baca'])->assertOk()->assertJsonPath('kemampuan', ['penomoran-sk:baca'])->json('token');
        $sk = SuratKeputusan::firstOrFail();

        $this->withToken($token)->getJson('/api/penomoran-sk')->assertOk();
        $this->withToken($token)->getJson("/api/penomoran-sk/{$sk->id}")->assertOk();
        $this->withToken($token)->getJson('/api/auth/saya')->assertOk()->assertJsonPath('kemampuan_token', ['penomoran-sk:baca']);

        $this->withToken($token)->postJson('/api/penomoran-sk', $this->data())->assertForbidden()
            ->assertJsonPath('message', 'Token ini tidak memiliki kemampuan untuk aksi ini. Masuk ulang dan minta kemampuan yang sesuai.')
            ->assertJsonPath('kemampuan_dibutuhkan', ['penomoran-sk:tulis']);
        $this->withToken($token)->putJson("/api/penomoran-sk/{$sk->id}", ['nama_peminta' => 'Kepala TU'])->assertForbidden();
        $this->assertSame(6, SuratKeputusan::count());
    }

    public function test_a_write_token_reads_too_and_takes_a_number(): void
    {
        $token = $this->login(['penomoran-sk:tulis'])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/penomoran-sk')->assertOk();
        $this->withToken($token)->postJson('/api/penomoran-sk', $this->data())->assertCreated();
    }

    public function test_without_kemampuan_the_token_does_what_the_account_may(): void
    {
        $token = $this->login()->assertOk()->assertJsonPath('kemampuan', ['*'])->json('token');

        $this->withToken($token)->postJson('/api/penomoran-sk', $this->data())->assertCreated();
        $this->assertSame(['*'], PersonalAccessToken::findToken($token)->abilities);
    }

    public function test_unknown_kemampuan_are_refused_at_login(): void
    {
        $this->login(['penomoran-sk:hapus-semua'])->assertJsonValidationErrors(['kemampuan.0' => 'Kemampuan token yang tersedia: penomoran-sk:baca, penomoran-sk:tulis, jenis-sk:kelola.']);
        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_a_token_for_something_else_does_not_open_the_register(): void
    {
        $token = $this->officer->createToken('Aplikasi lain', ['laporan:baca'])->plainTextToken;

        $this->withToken($token)->getJson('/api/penomoran-sk')->assertForbidden()
            ->assertJsonPath('kemampuan_dibutuhkan', ['penomoran-sk:baca', 'penomoran-sk:tulis']);
    }

    public function test_the_role_still_decides_whatever_the_token_may(): void
    {
        $frontDesk = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        $token = $frontDesk->createToken('Loket', ['penomoran-sk:tulis'])->plainTextToken;

        $this->withToken($token)->getJson('/api/penomoran-sk')->assertForbidden();
        $this->withToken($token)->postJson('/api/penomoran-sk', $this->data())->assertForbidden();
    }
}
