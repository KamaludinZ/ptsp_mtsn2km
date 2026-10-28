<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/penomoran-sk/{id}: Rincian SK for API clients. */
class RegisterSkDetailApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function sk(int $nomorUrut): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->with('pembuat')->firstOrFail();
    }

    public function test_the_detail_holds_everything_recorded_about_the_sk(): void
    {
        $sk = $this->sk(2);
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->getJson("/api/penomoran-sk/{$sk->id}")->assertOk()
            ->assertJson([
                'id' => $sk->id,
                'nomor_sk' => $sk->nomor_sk,
                'nomor_urut' => 2,
                'tahun' => now()->year,
                'tanggal_surat' => $sk->tanggal_surat->toDateString(),
                'perihal' => $sk->perihal,
                'jenis_sk' => $sk->jenis_sk,
                'nama_peminta' => $sk->nama_peminta,
                'keterangan' => 'Lampiran daftar pembagian tugas 4 halaman.',
                'status' => 'menunggu_scan',
                'petugas' => $sk->pembuat->name,
                'petugas_label' => $sk->pembuat->name . ' · ' . RoleAccess::roleLabel('back_office'),
                'arsip' => null,
                'perubahan_terakhir' => null,
                'wewenang' => ['ubah' => true, 'hapus' => false],
            ])
            ->assertJsonMissingPath('berkas_scan');
    }

    public function test_an_archived_sk_tells_about_its_scan_without_its_stored_path(): void
    {
        $sk = $this->sk(1);
        Sanctum::actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->postJson("/api/penomoran-sk/{$sk->id}/scan", ['berkas_scan' => UploadedFile::fake()->createWithContent('SK wali kelas.pdf', str_repeat('%PDF-1.4 ', 1000))])->assertOk();

        $response = $this->getJson("/api/penomoran-sk/{$sk->id}")->assertOk()
            ->assertJsonPath('status', 'terarsip')
            ->assertJsonPath('arsip.nama', 'SK wali kelas.pdf')
            ->assertJsonPath('arsip.jenis', 'PDF')
            ->assertJsonPath('arsip.mime', 'application/pdf')
            ->assertJsonPath('arsip.ukuran', 9000)
            ->assertJsonPath('arsip.diunggah_oleh', $officer->name);

        $this->assertNotNull($response->json('arsip.diunggah_pada'));
        $this->assertStringNotContainsString($sk->fresh()->berkas_scan, $response->getContent());
    }

    public function test_the_last_change_tells_who_in_which_role_and_when(): void
    {
        $sk = $this->sk(3);
        Sanctum::actingAs($admin = $this->user('ptsp@mtsn2malang.sch.id'), ['*']);
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['nama_peminta' => 'Kepala Madrasah'])->assertOk();

        $response = $this->getJson("/api/penomoran-sk/{$sk->id}")->assertOk()
            ->assertJsonPath('perubahan_terakhir.oleh', $admin->name)
            ->assertJsonPath('wewenang', ['ubah' => true, 'hapus' => true]);

        $role = $response->json('perubahan_terakhir.peran.nama');
        $this->assertNotNull($role);
        $this->assertSame(RoleAccess::roleLabel($role), $response->json('perubahan_terakhir.peran.label'));
        $this->assertNotNull($response->json('perubahan_terakhir.pada'));
    }

    public function test_a_change_without_an_account_and_a_removed_officer_are_named(): void
    {
        $sk = $this->sk(4);
        app(SuratKeputusanService::class)->describe($sk, ['keterangan' => 'Disesuaikan otomatis.']);
        $sk->pembuat->forceDelete();
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'), ['*']);

        $this->getJson("/api/penomoran-sk/{$sk->id}")->assertOk()
            ->assertJsonPath('petugas', null)
            ->assertJsonPath('petugas_label', 'Akun sudah dihapus · ' . RoleAccess::roleLabel('back_office'))
            ->assertJsonPath('perubahan_terakhir.oleh', 'Sistem')
            ->assertJsonPath('perubahan_terakhir.peran', null);
    }

    public function test_only_the_back_office_reads_an_existing_sk(): void
    {
        $sk = $this->sk(1);

        $this->getJson("/api/penomoran-sk/{$sk->id}")->assertUnauthorized();

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);
        $this->getJson("/api/penomoran-sk/{$sk->id}")->assertForbidden();

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->getJson('/api/penomoran-sk/999999')->assertNotFound();
    }

    public function test_rincian_sk_in_the_panel_shows_the_archive_details(): void
    {
        $sk = $this->sk(5);
        $this->actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'));
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->createWithContent('SK BOS.pdf', str_repeat('x', 1536 * 1024)));

        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertSeeInOrder(['Ukuran', '1,5 MB'])
            ->assertSeeInOrder(['Diunggah', $sk->fresh()->scan_diunggah_pada->translatedFormat('d M Y H:i'), 'oleh ' . $officer->name]);
    }
}
