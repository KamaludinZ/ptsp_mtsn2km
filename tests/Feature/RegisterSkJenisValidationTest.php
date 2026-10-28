<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\JenisSkService;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** JenisSkService: one list of jenis SK for the panel, the API and the service. */
class RegisterSkJenisValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function switchOff(string $nama): void
    {
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', $nama)->update(['is_active' => false]);
    }

    private function reserve(?string $jenis): SuratKeputusan
    {
        return app(SuratKeputusanService::class)->reserve(Carbon::parse(now()->year . '-06-01'), $this->officer, [
            'perihal' => 'Panitia ujian madrasah', 'jenis_sk' => $jenis, 'nama_peminta' => 'Waka Kurikulum',
        ]);
    }

    private function lastIssued(): ?int
    {
        return DB::table('surat_keputusan_sequences')->where('tahun', now()->year)->value('last_number');
    }

    public function test_the_list_is_the_active_master_jenis_plus_the_sks_own(): void
    {
        [, , , , $keuangan] = Persuratan::JENIS_SK;
        $this->assertSame(Persuratan::JENIS_SK, JenisSkService::allowed());

        $this->switchOff($keuangan);
        $sk = SuratKeputusan::where('jenis_sk', $keuangan)->firstOrFail();

        $this->assertNotContains($keuangan, JenisSkService::allowed());
        $this->assertSame($keuangan, collect(JenisSkService::allowed($sk->jenis_sk))->last());
    }

    public function test_case_and_spacing_resolve_to_the_master_spelling(): void
    {
        [$kepegawaian] = Persuratan::JENIS_SK;

        $this->assertSame($kepegawaian, JenisSkService::resolve('  KEPEGAWAIAN   dan pengangkatan '));
        $this->assertNull(JenisSkService::resolve('Kepegawaian'));
        $this->assertNull(JenisSkService::resolve(''));
        $this->assertNull(JenisSkService::resolve(null));
    }

    public function test_the_service_refuses_an_unknown_jenis_before_taking_a_number(): void
    {
        $before = $this->lastIssued();

        foreach (['SK Lainnya', '', null] as $jenis) {
            try {
                $this->reserve($jenis);
                $this->fail('Jenis was accepted: ' . json_encode($jenis));
            } catch (TicketActionException $e) {
                $this->assertSame(JenisSkService::MESSAGE, $e->getMessage());
            }
        }

        $this->assertSame($before, $this->lastIssued());
        $this->assertSame(Persuratan::JENIS_SK[1], $this->reserve(mb_strtolower(Persuratan::JENIS_SK[1]))->jenis_sk);
    }

    public function test_a_correction_keeps_a_switched_off_jenis_but_cannot_move_to_one(): void
    {
        [$kepegawaian, , , , $keuangan] = Persuratan::JENIS_SK;
        $this->switchOff($keuangan);
        $service = app(SuratKeputusanService::class);
        $own = SuratKeputusan::where('jenis_sk', $keuangan)->firstOrFail();
        $other = SuratKeputusan::where('jenis_sk', $kepegawaian)->firstOrFail();

        $service->describe($own, ['jenis_sk' => $keuangan, 'nama_peminta' => 'Bendahara']);
        $this->assertSame('Bendahara', $own->fresh()->nama_peminta);

        $this->expectException(TicketActionException::class);
        $service->describe($other, ['jenis_sk' => $keuangan]);
    }

    public function test_the_api_stores_the_master_spelling_and_refuses_the_rest(): void
    {
        [, , $panitia] = Persuratan::JENIS_SK;
        Sanctum::actingAs($this->officer, ['*']);
        $data = ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Panitia ujian akhir', 'nama_peminta' => 'Waka Kurikulum'];

        $this->postJson('/api/penomoran-sk', [...$data, 'jenis_sk' => mb_strtoupper($panitia)])->assertCreated()
            ->assertJsonPath('jenis_sk', $panitia);

        $this->postJson('/api/penomoran-sk', [...$data, 'jenis_sk' => 'SK Lainnya'])
            ->assertJsonValidationErrors(['jenis_sk' => JenisSkService::MESSAGE]);
    }

    public function test_the_panel_offers_the_same_list(): void
    {
        [, , , , $keuangan] = Persuratan::JENIS_SK;
        $this->switchOff($keuangan);
        $sk = SuratKeputusan::where('jenis_sk', $keuangan)->firstOrFail();
        $this->actingAs($this->officer);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', ['tanggal_surat' => now()->toDateString(), 'jenis_sk' => $keuangan, 'perihal' => 'Bendahara', 'nama_peminta' => 'Kepala TU'])
            ->assertHasActionErrors(['jenis_sk' => 'in']);

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['nama_peminta' => 'Bendahara'])
            ->assertHasNoTableActionErrors();
        $this->assertSame([$keuangan, 'Bendahara'], [$sk->fresh()->jenis_sk, $sk->fresh()->nama_peminta]);
    }
}
