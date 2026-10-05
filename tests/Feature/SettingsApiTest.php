<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /api/pengaturan: profil instansi and logo. */
class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create(['user_type' => 'pegawai'])->assignRole(Role::findByName('admin', 'web'));
        Sanctum::actingAs($this->admin);
    }

    public function test_only_administrators(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole(Role::findByName('kepala_sekolah', 'web')));

        $this->getJson('/api/pengaturan/profil')->assertForbidden();
        $this->patchJson('/api/pengaturan/profil', ['nama' => 'X'])->assertForbidden();
        $this->postJson('/api/pengaturan/profil/logo')->assertForbidden();
    }

    public function test_read_and_update_the_profile(): void
    {
        AppSetting::set('app_name', 'PTSP');
        AppSetting::set('contact_phone', '0341-111');

        $this->getJson('/api/pengaturan/profil')->assertOk()
            ->assertJsonPath('nama', 'PTSP')->assertJsonPath('telepon', '0341-111')->assertJsonPath('npsn', null);

        $this->patchJson('/api/pengaturan/profil', [
            'nama_lengkap' => '  MTsN 2 Kota Malang ',
            'npsn' => '20533850',
            'telepon' => '',
            'jam_jumat' => '07.00–11.00',
        ])->assertOk()
            ->assertJsonPath('diubah', ['Nama lengkap instansi', 'NPSN', 'Telepon', 'Jumat'])
            ->assertJsonPath('data.nama_lengkap', 'MTsN 2 Kota Malang')
            ->assertJsonPath('data.telepon', null)
            ->assertJsonPath('data.nama', 'PTSP');

        $this->assertSame($this->admin->id, AppSetting::where('key', 'institution_npsn')->value('updated_by'));
        $this->assertSame(['Nama lengkap instansi', 'NPSN', 'Telepon', 'Jumat'], Activity::inLog('audit')->latest('id')->first()->properties['diubah']);

        $this->patchJson('/api/pengaturan/profil', ['npsn' => '20533850'])->assertOk()
            ->assertJsonPath('message', 'Tidak ada perubahan.')->assertJsonPath('diubah', []);
    }

    public function test_profile_validation(): void
    {
        $this->patchJson('/api/pengaturan/profil', [
            'nama' => '', 'npsn' => '123', 'nsm' => 'abc', 'email' => 'bukan-email',
            'situs' => 'ftp://x', 'whatsapp_ptsp' => 'hubungi kami', 'nip_kepala_madrasah' => '12',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'nama' => 'wajib diisi', 'npsn' => '8 angka', 'nsm' => '12 angka', 'email', 'situs',
            'whatsapp_ptsp' => 'hanya berisi angka', 'nip_kepala_madrasah' => '18 angka',
        ]);
    }

    public function test_upload_replace_and_remove_the_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/bawaan.txt', 'x');

        $first = $this->post('/api/pengaturan/profil/logo', ['logo' => UploadedFile::fake()->image('logo.png', 256, 256)], ['Accept' => 'application/json'])
            ->assertCreated()->json('logo');
        $firstPath = AppSetting::get('app_logo');
        $this->assertStringStartsWith('storage/branding/logo-', $firstPath);
        $this->assertStringEndsWith($firstPath, $first);
        Storage::disk('public')->assertExists(substr($firstPath, 8));
        $this->getJson('/api/pengaturan/profil')->assertJsonPath('logo_diunggah', true);

        $this->post('/api/pengaturan/profil/logo', ['logo' => UploadedFile::fake()->image('baru.jpg', 300, 300)], ['Accept' => 'application/json'])->assertCreated();
        Storage::disk('public')->assertMissing(substr($firstPath, 8));
        $second = AppSetting::get('app_logo');

        $this->deleteJson('/api/pengaturan/profil/logo')->assertNoContent();
        Storage::disk('public')->assertMissing(substr($second, 8));
        Storage::disk('public')->assertExists('branding/bawaan.txt');
        $this->assertNull(AppSetting::get('app_logo'));
        $this->deleteJson('/api/pengaturan/profil/logo')->assertNotFound();
    }

    public function test_logo_validation_and_bundled_logo_is_never_deleted(): void
    {
        Storage::fake('public');
        AppSetting::set('app_logo', 'images/logo.png');

        $this->post('/api/pengaturan/profil/logo', ['logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertJsonValidationErrors(['logo' => 'gambar']);
        $this->post('/api/pengaturan/profil/logo', ['logo' => UploadedFile::fake()->image('kecil.png', 20, 20)], ['Accept' => 'application/json'])
            ->assertJsonValidationErrors(['logo' => 'minimal 64']);
        $this->post('/api/pengaturan/profil/logo', ['logo' => UploadedFile::fake()->image('besar.png', 300, 300)->size(3000)], ['Accept' => 'application/json'])
            ->assertJsonValidationErrors(['logo' => '2 MB']);

        $this->getJson('/api/pengaturan/profil')->assertJsonPath('logo_diunggah', false);
        $this->deleteJson('/api/pengaturan/profil/logo')->assertNoContent();
        $this->assertNull(AppSetting::get('app_logo'));
    }

    public function test_general_settings_format_and_numbering(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-05 09:00'));

        $this->getJson('/api/pengaturan/umum')->assertOk()
            ->assertJsonPath('awalan_tiket', null)
            ->assertJsonPath('mode_perawatan', false)
            ->assertJsonPath('contoh.nomor_tiket', 'PTSP-202610-0007')
            ->assertJsonPath('contoh.tanggal', '5 Oktober 2026')
            ->assertJsonPath('pilihan_format_tanggal.3.contoh', 'Senin, 5 Oktober 2026');

        $this->patchJson('/api/pengaturan/umum', [
            'awalan_tiket' => 'mts', 'kode_satker_surat' => 'MTsN.2-KM', 'format_tanggal' => 'd/m/Y',
            'kop_baris_1' => 'Kementerian Agama Republik Indonesia', 'mode_perawatan' => true, 'instagram' => 'https://instagram.com/mtsn2',
        ])->assertOk()
            ->assertJsonPath('diubah', ['Awalan nomor tiket', 'Kode satker surat keluar', 'Format tanggal dokumen', 'Baris kop 1', 'Mode perawatan', 'Instagram'])
            ->assertJsonPath('data.awalan_tiket', 'MTS')
            ->assertJsonPath('data.mode_perawatan', true)
            ->assertJsonPath('data.contoh.nomor_tiket', 'MTS-202610-0007')
            ->assertJsonPath('data.contoh.nomor_surat_keluar', 'B-12/MTsN.2-KM/PP.00/10/2026')
            ->assertJsonPath('data.contoh.tanggal', '05/10/2026');

        $this->assertSame('MTS', \App\Support\Formats::ticketPrefix());

        $this->patchJson('/api/pengaturan/umum', ['mode_perawatan' => false, 'awalan_tiket' => null])->assertOk()
            ->assertJsonPath('data.mode_perawatan', false)->assertJsonPath('data.contoh.nomor_tiket', 'PTSP-202610-0007');
    }

    public function test_general_settings_validation(): void
    {
        $this->patchJson('/api/pengaturan/umum', [
            'awalan_tiket' => 'PT 1', 'kode_satker_surat' => 'x', 'format_tanggal' => 'Y-m-d', 'youtube' => 'javascript:alert(1)', 'mode_perawatan' => 'mungkin',
        ])->assertJsonValidationErrors([
            'awalan_tiket' => '2–8 huruf', 'kode_satker_surat' => '2–20 karakter', 'format_tanggal' => 'j F Y', 'youtube', 'mode_perawatan',
        ]);
        $this->assertNull(AppSetting::get('ticket_prefix'));
    }
}
