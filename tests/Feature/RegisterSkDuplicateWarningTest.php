<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Formulir SK: a perihal much like an SK of the same year is flagged before a second number is taken. */
class RegisterSkDuplicateWarningTest extends TestCase
{
    use RefreshDatabase;

    private SuratKeputusan $ppdb;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        // Seeded: "Panitia penerimaan peserta didik baru".
        $this->ppdb = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 3)->firstOrFail();
    }

    public function test_similar_perihal_of_the_same_year_are_found(): void
    {
        $year = now()->year;

        $this->assertSame([$this->ppdb->id], SuratKeputusanService::similarPerihal('Tentang panitia penerimaan peserta didik  baru gelombang 2', $year)->pluck('id')->all());
        $this->assertEmpty(SuratKeputusanService::similarPerihal('Panitia class meeting', $year));
        $this->assertEmpty(SuratKeputusanService::similarPerihal('Panitia', $year));
        $this->assertEmpty(SuratKeputusanService::similarPerihal('Panitia penerimaan peserta didik baru', $year - 1));
        $this->assertEmpty(SuratKeputusanService::similarPerihal('Panitia penerimaan peserta didik baru', $year, except: $this->ppdb->id));
    }

    public function test_the_request_form_warns_about_a_double_request_without_blocking_it(): void
    {
        $warning = 'Mirip SK yang sudah ada: ' . $this->ppdb->nomor_sk . ' (Panitia penerimaan peserta didik baru). Pastikan ini bukan permintaan ganda.';

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Pokok keputusan seperti tertulis pada judul SK.')
            ->setActionData(['perihal' => 'Panitia penerimaan peserta didik baru gelombang 2'])
            ->assertSeeHtml('data-sk-similar')
            ->assertSee($warning)
            ->setActionData(['tanggal_surat' => (now()->year + 1) . '-01-05'])
            ->assertDontSeeHtml('data-sk-similar');

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', [
                'tanggal_surat' => now()->toDateString(), 'jenis_sk' => $this->ppdb->jenis_sk,
                'perihal' => 'Panitia penerimaan peserta didik baru gelombang 2', 'nama_peminta' => 'Waka Kesiswaan',
            ])
            ->assertHasNoActionErrors();
        $this->assertSame(1, SuratKeputusan::where('perihal', 'Panitia penerimaan peserta didik baru gelombang 2')->count());
    }

    public function test_correcting_an_sk_does_not_flag_the_sk_itself(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $this->ppdb)
            ->assertDontSeeHtml('data-sk-similar')
            ->assertSee('Pokok keputusan seperti tertulis pada judul SK.');
    }

    public function test_the_registration_page_warns_too(): void
    {
        Livewire::test(RegisterSkNumber::class)
            ->fillForm(['perihal' => 'panitia penerimaan peserta didik baru'])
            ->assertSeeHtml('data-sk-similar');
    }
}
