<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Minta Nomor SK: saving gives the number, ready to copy, or keeps the form for the next SK. */
class RegisterSkSaveActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    private function data(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'perihal' => 'Panitia class meeting',
            'nama_peminta' => 'Waka Kesiswaan',
            ...$override,
        ];
    }

    public function test_modal_offers_ambil_nomor_and_ambil_dan_minta_lagi(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Ambil nomor')
            ->assertSee('Ambil &amp; minta lagi', escape: false);
    }

    public function test_saved_number_comes_with_a_copy_button_and_the_scan_reminder(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data())
            ->assertHasNoActionErrors()
            ->assertNotified('Nomor SK diberikan');

        $notification = ListSuratKeputusan::numberGiven(SuratKeputusan::latest('id')->firstOrFail());
        $this->assertSame("7 TAHUN {$year}<br>Tentang Panitia class meeting<br><span class=\"text-xs\">Scan belum diunggah; unggah lewat \"Ubah\" setelah SK ditandatangani.</span>", $notification->getBody());
        $copy = $notification->getActions()[0];
        $this->assertSame('Salin nomor', $copy->getLabel());
        $this->assertSame("7 TAHUN {$year}", $copy->getExtraAttributes()['data-copy-number']);
        // The button escapes its attributes more than once: no quotes for Alpine to trip on.
        $this->assertDoesNotMatchRegularExpression('/[\'"&<>]/', $copy->getExtraAttributes()['x-on:click']);
    }

    public function test_ambil_dan_minta_lagi_keeps_the_form_open_with_date_and_jenis(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData($this->data())
            ->callMountedAction(['another' => true])
            ->assertHasNoActionErrors()
            ->assertActionMounted('reserve')
            ->assertActionDataSet(['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'perihal' => null, 'nama_peminta' => null])
            ->assertSeeHtml('data-sk-preview="8 TAHUN ' . $year . '"')
            ->setActionData(['perihal' => 'Panitia pentas akhir tahun', 'nama_peminta' => 'Pembina OSIM'])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertActionNotMounted('reserve');

        $this->assertSame(
            ["7 TAHUN {$year}" => 'Panitia class meeting', "8 TAHUN {$year}" => 'Panitia pentas akhir tahun'],
            SuratKeputusan::where('nomor_urut', '>', 6)->orderBy('nomor_urut')->pluck('perihal', 'nomor_sk')->all(),
        );
    }

    public function test_a_refused_number_keeps_what_was_typed(): void
    {
        $year = now()->year;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9999, 'nomor_sk' => "9999 TAHUN {$year}", 'tanggal_surat' => now(), 'perihal' => 'Terakhir', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji']);

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData($this->data())
            ->callMountedAction()
            ->assertNotified('Nomor SK tidak dapat diberikan')
            ->assertActionMounted('reserve')
            ->assertActionDataSet(['perihal' => 'Panitia class meeting']);

        $this->assertSame(0, SuratKeputusan::where('perihal', 'Panitia class meeting')->count());
    }
}
