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

/** GET /api/penomoran-sk tells what a report of the same filters holds, as the panel does above the register. */
class RegisterSkListReportApiTest extends TestCase
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
        $this->actingAs($this->officer);
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 3)->firstOrFail();
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->createWithContent('SK PPDB.pdf', '%PDF-1.4'));
    }

    public function test_the_list_names_its_period_and_sums_up_every_page(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk?per_halaman=2')->assertOk()
            ->assertJsonPath('periode', 'Register tahun ' . now()->year)
            ->assertJsonPath('total', 6)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('ringkasan', ['terarsip' => 1, 'menunggu_scan' => 5]);

        $this->getJson('/api/penomoran-sk?tahun=semua&jenis_sk=' . urlencode('Kepanitiaan dan Tim Pelaksana Kegiatan'))->assertOk()
            ->assertJsonPath('periode', 'Register semua tahun')
            ->assertJsonPath('total', 2)
            ->assertJsonPath('ringkasan', ['terarsip' => 1, 'menunggu_scan' => 1]);

        $this->getJson('/api/penomoran-sk?status=menunggu_scan')->assertOk()
            ->assertJsonPath('ringkasan', ['terarsip' => 0, 'menunggu_scan' => 5]);
        $this->getJson('/api/penomoran-sk?q=tidak ada perihal seperti ini')->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('ringkasan', ['terarsip' => 0, 'menunggu_scan' => 0]);
    }

    public function test_the_api_and_the_panel_describe_the_same_report(): void
    {
        $page = Livewire::test(ListSuratKeputusan::class)->filterTable('status', 'menunggu_scan');
        $panel = [$page->instance()->reportPeriod(), $page->instance()->getAllTableRecordsCount()];

        Sanctum::actingAs($this->officer, ['*']);
        $api = $this->getJson('/api/penomoran-sk?status=menunggu_scan')->assertOk();

        $this->assertSame($panel, [$api->json('periode'), $api->json('total')]);
    }
}
