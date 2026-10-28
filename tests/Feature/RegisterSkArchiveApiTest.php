<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/penomoran-sk/arsip: the archived SK scans. */
class RegisterSkArchiveApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function sk(int $nomorUrut): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->firstOrFail();
    }

    /** Scans for SK 1 (PDF, 3000 bytes), 3 (PNG) and 5 (PDF, 1000 bytes), uploaded an hour apart in the order 5, 1, 3. */
    private function archiveSome(): void
    {
        $this->actingAs($this->officer);
        $service = app(SuratKeputusanService::class);

        $service->attachScan($this->sk(5), UploadedFile::fake()->createWithContent('SK BOS.pdf', str_repeat('x', 1000)));
        $this->travel(1)->hour();
        $service->attachScan($this->sk(1), UploadedFile::fake()->createWithContent('SK wali kelas.pdf', str_repeat('x', 3000)));
        $this->travel(1)->hour();
        $service->attachScan($this->sk(3), UploadedFile::fake()->image('SK PPDB.png'));
    }

    public function test_the_archive_lists_each_scan_newest_number_first(): void
    {
        $this->archiveSome();
        Sanctum::actingAs($this->officer, ['*']);

        $response = $this->getJson('/api/penomoran-sk/arsip')->assertOk()
            ->assertJsonPath('tahun', now()->year)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('data.*.nomor_urut', [5, 3, 1])
            ->assertJsonPath('data.0.nomor_sk', '5 TAHUN ' . now()->year)
            ->assertJsonPath('data.0.berkas.nama', 'SK BOS.pdf')
            ->assertJsonPath('data.0.berkas.jenis', 'PDF')
            ->assertJsonPath('data.0.berkas.ukuran', 1000)
            ->assertJsonPath('data.0.berkas.diunggah_oleh', $this->officer->name)
            ->assertJsonPath('data.1.berkas.jenis', 'PNG')
            ->assertJsonPath('ringkasan.menunggu_scan', 3)
            ->assertJsonStructure(['data' => [['id', 'perihal', 'jenis_sk', 'nama_peminta', 'tanggal_surat', 'berkas' => ['mime', 'diunggah_pada']]]]);

        $this->assertSame(4000 + $this->sk(3)->berkas_scan_ukuran, $response->json('ringkasan.total_ukuran'));
        foreach ([1, 3, 5] as $nomor) {
            $this->assertStringNotContainsString($this->sk($nomor)->berkas_scan, $response->getContent());
        }
    }

    public function test_the_archive_filters_as_the_register_and_by_file_type(): void
    {
        $this->archiveSome();
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk/arsip?jenis_berkas=pdf')->assertOk()
            ->assertJsonPath('data.*.nomor_urut', [5, 1])
            ->assertJsonPath('ringkasan.total_ukuran', 4000)
            // Still waiting: jenis_berkas does not narrow that count.
            ->assertJsonPath('ringkasan.menunggu_scan', 3);

        $this->getJson('/api/penomoran-sk/arsip?q=wali kelas')->assertOk()->assertJsonPath('data.*.nomor_urut', [1]);
        $this->getJson('/api/penomoran-sk/arsip?' . http_build_query(['jenis_sk' => $this->sk(3)->jenis_sk]))->assertOk()
            ->assertJsonPath('data.*.nomor_urut', [3])
            ->assertJsonPath('ringkasan.menunggu_scan', 1);
        $this->getJson('/api/penomoran-sk/arsip?tahun=' . (now()->year - 1))->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('ringkasan', ['total_ukuran' => 0, 'menunggu_scan' => 0]);
        // Status is always terarsip here.
        $this->getJson('/api/penomoran-sk/arsip?status=menunggu_scan')->assertOk()->assertJsonPath('total', 3);
    }

    public function test_the_archive_sorts_by_upload_time(): void
    {
        $this->archiveSome();
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk/arsip?urutkan=diunggah&arah=asc')->assertOk()->assertJsonPath('data.*.nomor_urut', [5, 1, 3]);
        $this->getJson('/api/penomoran-sk/arsip?urutkan=diunggah')->assertOk()->assertJsonPath('data.*.nomor_urut', [3, 1, 5]);
        $this->getJson('/api/penomoran-sk/arsip?per_halaman=2&page=2')->assertOk()
            ->assertJsonPath('data.*.nomor_urut', [1])
            ->assertJsonPath('halaman_terakhir', 2);
    }

    public function test_invalid_archive_filters_are_refused(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk/arsip?jenis_berkas=docx')->assertJsonValidationErrors('jenis_berkas');
        $this->getJson('/api/penomoran-sk/arsip?urutkan=ukuran')->assertJsonValidationErrors('urutkan');
        $this->getJson('/api/penomoran-sk/arsip?tahun=semuanya')->assertJsonValidationErrors('tahun');
        // urutkan=diunggah belongs to the archive only.
        $this->getJson('/api/penomoran-sk?urutkan=diunggah')->assertJsonValidationErrors('urutkan');
    }

    public function test_only_the_back_office_lists_the_archive(): void
    {
        $this->getJson('/api/penomoran-sk/arsip')->assertUnauthorized();

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson('/api/penomoran-sk/arsip')->assertForbidden();
    }
}
