<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Unggah Scan SK: one file per SK on the private disk, old files cleaned up. */
class RegisterSkScanStorageTest extends TestCase
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

    private function first(): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
    }

    private function upload(SuratKeputusan $sk, mixed $file)
    {
        return $this->post("/api/penomoran-sk/{$sk->id}/scan", ['berkas_scan' => $file], ['Accept' => 'application/json']);
    }

    public function test_a_scan_that_follows_later_archives_the_sk(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = $this->first();

        $this->upload($sk, UploadedFile::fake()->create('SK wali kelas.pdf', 400, 'application/pdf'))->assertOk()
            ->assertJsonPath('status', 'terarsip')
            ->assertJsonPath('nama_scan', 'SK wali kelas.pdf')
            ->assertJsonMissingPath('berkas_scan');

        $sk->refresh();
        $this->assertStringStartsWith(SuratKeputusan::SCAN_DIRECTORY . '/', $sk->berkas_scan);
        $this->assertNotSame('SK wali kelas.pdf', basename($sk->berkas_scan));
        Storage::disk('local')->assertExists($sk->berkas_scan);
        $this->assertDatabaseHas('activity_log', ['log_name' => 'surat_keputusan', 'subject_id' => $sk->id, 'event' => 'updated']);
    }

    public function test_replacing_the_scan_removes_the_earlier_file(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = $this->first();
        $this->upload($sk, UploadedFile::fake()->create('draf.pdf', 100, 'application/pdf'))->assertOk();
        $old = $sk->fresh()->berkas_scan;

        $this->upload($sk, UploadedFile::fake()->image('SK ditandatangani.jpg'))->assertOk()->assertJsonPath('nama_scan', 'SK ditandatangani.jpg');

        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($sk->fresh()->berkas_scan);
        $this->assertCount(1, Storage::disk('local')->allFiles(SuratKeputusan::SCAN_DIRECTORY));
    }

    public function test_removing_the_scan_sends_the_sk_back_to_waiting(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = $this->first();
        $this->upload($sk, UploadedFile::fake()->create('SK.pdf', 100, 'application/pdf'))->assertOk();
        $path = $sk->fresh()->berkas_scan;

        $this->deleteJson("/api/penomoran-sk/{$sk->id}/scan")->assertOk()
            ->assertJsonPath('status', 'menunggu_scan')
            ->assertJsonPath('nama_scan', null);

        $this->assertNull($sk->fresh()->berkas_scan_nama);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_only_pdf_or_images_up_to_10_mb_are_kept(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = $this->first();

        $this->upload($sk, null)->assertJsonValidationErrors(['berkas_scan' => 'Pilih file']);
        $this->upload($sk, 'surat-keputusan/lain.pdf')->assertJsonValidationErrors(['berkas_scan' => 'sebagai file']);
        $this->upload($sk, UploadedFile::fake()->create('sk.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'))
            ->assertJsonValidationErrors(['berkas_scan' => 'PDF, JPG, atau PNG']);
        $this->upload($sk, UploadedFile::fake()->create('besar.pdf', SuratKeputusan::SCAN_MAX_SIZE + 1, 'application/pdf'))
            ->assertJsonValidationErrors(['berkas_scan' => '10 MB']);

        $this->assertNull($sk->fresh()->berkas_scan);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_front_desk_cannot_upload_or_remove_a_scan(): void
    {
        $sk = $this->first();
        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);

        $this->upload($sk, UploadedFile::fake()->create('SK.pdf', 100, 'application/pdf'))->assertForbidden();
        $this->deleteJson("/api/penomoran-sk/{$sk->id}/scan")->assertForbidden();
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_replacing_the_scan_through_ubah_removes_the_earlier_file(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $sk = $this->first();

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['berkas_scan' => UploadedFile::fake()->create('draf.pdf', 100, 'application/pdf')])
            ->assertHasNoTableActionErrors();
        $old = $sk->fresh()->berkas_scan;

        // Replacing in the single-file picker: take the old file out, then drop the new one.
        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $sk)
            ->setTableActionData(['berkas_scan' => null])
            ->setTableActionData(['berkas_scan' => [UploadedFile::fake()->create('final.pdf', 100, 'application/pdf')]])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($sk->fresh()->berkas_scan);
        $this->assertSame('final.pdf', $sk->fresh()->berkas_scan_nama);
    }

    public function test_changing_other_data_keeps_the_scan(): void
    {
        $sk = $this->first();
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->create('SK.pdf', 100, 'application/pdf'));
        $path = $sk->fresh()->berkas_scan;

        app(SuratKeputusanService::class)->describe($sk->fresh(), ['perihal' => 'Pengangkatan wali kelas semester genap']);

        $this->assertSame($path, $sk->fresh()->berkas_scan);
        Storage::disk('local')->assertExists($path);
    }

    public function test_deleting_an_sk_removes_its_scan(): void
    {
        $sk = $this->first();
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->create('SK.pdf', 100, 'application/pdf'));
        $path = $sk->fresh()->berkas_scan;

        app(SuratKeputusanService::class)->delete($sk->fresh(), $this->user('ptsp@mtsn2malang.sch.id'), 'Salah input data');

        Storage::disk('local')->assertMissing($path);
    }

    public function test_the_kept_name_is_only_the_files_own_name(): void
    {
        $this->assertSame('passwd.pdf', SuratKeputusan::cleanScanName('../../etc/passwd.pdf'));
        $this->assertSame('SK 12.pdf', SuratKeputusan::cleanScanName("C:\\Scan\\SK\t 12.pdf"));
        $this->assertSame('SK.pdf', SuratKeputusan::cleanScanName("SK\x00.pdf"));
        $this->assertNull(SuratKeputusan::cleanScanName('  '));
        $this->assertSame(255, mb_strlen(SuratKeputusan::cleanScanName(str_repeat('a', 300) . '.pdf')));
    }
}
