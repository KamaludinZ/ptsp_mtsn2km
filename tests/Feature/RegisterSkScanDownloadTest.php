<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Unduh berkas arsip: the signed SK scan, opened or downloaded from Rincian SK and the register. */
class RegisterSkScanDownloadTest extends TestCase
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

    private function archived(): SuratKeputusan
    {
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->create('SK wali kelas.pdf', 100, 'application/pdf'));

        return $sk->fresh();
    }

    public function test_rincian_sk_links_to_the_scan(): void
    {
        $sk = $this->archived();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()
            ->assertSee('Lihat scan')
            ->assertSee('Unduh scan')
            ->assertSee(route('surat-keputusan.scan', $sk), false)
            ->assertSee(route('surat-keputusan.scan', [$sk, 'unduh' => 1]), false);
    }

    public function test_the_scan_opens_in_the_browser_or_downloads_under_its_own_name(): void
    {
        $sk = $this->archived();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $inline = $this->get(route('surat-keputusan.scan', $sk))->assertOk();
        $this->assertStringStartsWith('inline', $inline->headers->get('content-disposition'));
        $this->assertStringContainsString('SK wali kelas.pdf', $inline->headers->get('content-disposition'));

        $this->get(route('surat-keputusan.scan', [$sk, 'unduh' => 1]))->assertDownload('SK wali kelas.pdf');
    }

    public function test_an_sk_waiting_for_its_scan_has_nothing_to_download(): void
    {
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $this->get(SuratKeputusanResource::getUrl('view', ['record' => $sk]))->assertOk()->assertDontSee('Unduh scan');
        $this->get(route('surat-keputusan.scan', $sk))->assertNotFound();
    }

    public function test_the_register_offers_the_scan_only_for_archived_sk(): void
    {
        $sk = $this->archived();
        $waiting = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeputusan::class)
            ->assertTableActionVisible('scan', $sk)
            ->assertTableActionHasUrl('scan', route('surat-keputusan.scan', $sk), $sk)
            ->assertTableActionHidden('scan', $waiting);
    }

    public function test_the_scan_is_served_as_its_checked_type_and_never_cached(): void
    {
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 3)->firstOrFail();
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->image('SK PPDB.png'));
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $response = $this->get(route('surat-keputusan.scan', $sk))->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('x-content-type-options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('cache-control'));
        $this->assertStringContainsString('private', $response->headers->get('cache-control'));
    }

    public function test_a_scan_of_unknown_type_is_only_ever_downloaded(): void
    {
        $sk = $this->archived();
        // An older scan whose type could not be told when it was archived.
        DB::table('surat_keputusan')->where('id', $sk->id)->update(['berkas_scan_mime' => null]);
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $response = $this->get(route('surat-keputusan.scan', $sk))->assertOk()
            ->assertHeader('content-type', 'application/octet-stream');
        $this->assertStringStartsWith('attachment', $response->headers->get('content-disposition'));
    }

    public function test_a_scan_missing_from_the_disk_is_reported(): void
    {
        $sk = $this->archived();
        Storage::disk('local')->delete($sk->berkas_scan);
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->getJson("/api/penomoran-sk/{$sk->id}/scan")->assertNotFound()
            ->assertJsonPath('message', 'Berkas scan SK tidak ditemukan di arsip; unggah ulang lewat "Ubah".');
    }

    public function test_api_clients_download_the_scan_with_their_token(): void
    {
        $sk = $this->archived();
        $waiting = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $link = $this->getJson("/api/penomoran-sk/{$sk->id}")->assertOk()->json('arsip.tautan_unduh');
        $this->assertSame(route('api.penomoran-sk.scan.show', [$sk, 'unduh' => 1]), $link);

        $this->get($link)->assertOk()->assertDownload('SK wali kelas.pdf')->assertHeader('content-type', 'application/pdf');
        $inline = $this->get("/api/penomoran-sk/{$sk->id}/scan")->assertOk();
        $this->assertStringStartsWith('inline', $inline->headers->get('content-disposition'));

        $this->getJson("/api/penomoran-sk/{$waiting->id}/scan")->assertNotFound()->assertJsonPath('message', 'SK ini belum memiliki scan.');
        $this->getJson('/api/penomoran-sk/999999/scan')->assertNotFound();

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);
        $this->getJson("/api/penomoran-sk/{$sk->id}/scan")->assertForbidden();
    }

    public function test_api_download_needs_a_token(): void
    {
        $this->getJson("/api/penomoran-sk/{$this->archived()->id}/scan")->assertUnauthorized();
    }

    public function test_only_the_back_office_gets_the_scan(): void
    {
        $sk = $this->archived();

        $this->get(route('surat-keputusan.scan', $sk))->assertRedirect();
        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'))
            ->get(route('surat-keputusan.scan', [$sk, 'unduh' => 1]))
            ->assertForbidden();
    }
}
