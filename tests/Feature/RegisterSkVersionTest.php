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

/** Versi SK: a correction made on what someone else changed meanwhile is refused, not saved over it. */
class RegisterSkVersionTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private SuratKeputusan $sk;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
    }

    /** Another officer's correction, made meanwhile. */
    private function someoneElseCorrects(): void
    {
        app(SuratKeputusanService::class)->describe($this->sk->fresh(), ['nama_peminta' => 'Kepala Madrasah']);
    }

    public function test_every_change_moves_the_versi_on_and_it_cannot_be_set(): void
    {
        $this->assertSame(1, $this->sk->versi);
        $this->someoneElseCorrects();
        $this->assertSame(2, $this->sk->fresh()->versi);

        $this->sk->fresh()->update(['keterangan' => 'Dua halaman.', 'versi' => 50]);
        $this->assertSame(3, $this->sk->fresh()->versi);

        $new = SuratKeputusan::create(['tahun' => now()->year, 'nomor_urut' => 40, 'nomor_sk' => SuratKeputusan::composeNumber(40, now()->year), 'tanggal_surat' => now()->toDateString(),
            'perihal' => 'Uji', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji']);
        $new->update(['keterangan' => 'Langsung dikoreksi.']);
        $this->assertSame(2, $new->fresh()->versi);
    }

    public function test_the_api_refuses_a_correction_on_an_old_versi(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $read = $this->getJson("/api/penomoran-sk/{$this->sk->id}")->assertOk()->assertJsonPath('versi', 1)->json('versi');

        $this->someoneElseCorrects();

        $this->putJson("/api/penomoran-sk/{$this->sk->id}", ['nama_peminta' => 'Waka Kurikulum', 'versi' => $read])->assertStatus(409)
            ->assertJsonPath('message', 'SK ' . $this->sk->nomor_sk . ' sudah diubah petugas lain sejak Anda membukanya. Muat ulang datanya, lalu ulangi koreksi Anda.')
            ->assertJsonPath('versi_sekarang', 2)
            ->assertJsonPath('data.nama_peminta', 'Kepala Madrasah');
        $this->assertSame('Kepala Madrasah', $this->sk->fresh()->nama_peminta);

        $this->putJson("/api/penomoran-sk/{$this->sk->id}", ['nama_peminta' => 'Waka Kurikulum', 'versi' => 2])->assertOk()
            ->assertJsonPath('versi', 3);
        $this->withHeader('If-Match', '"3"')->putJson("/api/penomoran-sk/{$this->sk->id}", ['keterangan' => 'Lewat If-Match.'])->assertOk()->assertJsonPath('versi', 4);
        $this->withHeader('If-Match', 'W/"3"')->putJson("/api/penomoran-sk/{$this->sk->id}", ['keterangan' => 'Basi.'])->assertStatus(409);
        // Without a versi, no check (older clients).
        $this->withHeaders(['If-Match' => ''])->putJson("/api/penomoran-sk/{$this->sk->id}", ['keterangan' => 'Tanpa versi.'])->assertOk();
    }

    public function test_scan_changes_on_an_old_versi_are_refused_too(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $this->someoneElseCorrects();

        $this->postJson("/api/penomoran-sk/{$this->sk->id}/scan", ['berkas_scan' => UploadedFile::fake()->createWithContent('SK.pdf', '%PDF-1.4'), 'versi' => 1])->assertStatus(409);
        $this->assertNull($this->sk->fresh()->berkas_scan);
        $this->assertSame([], Storage::disk('local')->allFiles(SuratKeputusan::SCAN_DIRECTORY));

        $this->postJson("/api/penomoran-sk/{$this->sk->id}/scan", ['berkas_scan' => UploadedFile::fake()->createWithContent('SK.pdf', '%PDF-1.4'), 'versi' => 2])->assertOk()
            ->assertJsonPath('versi', 3);
        $this->deleteJson("/api/penomoran-sk/{$this->sk->id}/scan?versi=2")->assertStatus(409);
        $this->deleteJson("/api/penomoran-sk/{$this->sk->id}/scan?versi=3")->assertOk()->assertJsonPath('ada_scan', false);
    }

    public function test_the_panel_keeps_the_form_when_someone_else_changed_the_sk(): void
    {
        $this->actingAs($this->officer);

        $page = Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $this->sk)
            ->assertTableActionDataSet(['versi' => 1]);

        $this->someoneElseCorrects();

        $page->setTableActionData(['nama_peminta' => 'Waka Kurikulum'])
            ->callMountedTableAction()
            ->assertNotified('Koreksi SK tidak disimpan')
            ->assertTableActionMounted('edit');
        $this->assertSame('Kepala Madrasah', $this->sk->fresh()->nama_peminta);

        // Opened again, the current versi saves.
        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $this->sk, ['nama_peminta' => 'Waka Kurikulum'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('Waka Kurikulum', $this->sk->fresh()->nama_peminta);
    }
}
