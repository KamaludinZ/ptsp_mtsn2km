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

/** Minta Nomor SK: the number preview follows the chosen date. */
class RegisterSkPreviewTest extends TestCase
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

    private function sk(int $tahun, int $urut): SuratKeputusan
    {
        return SuratKeputusan::create(['tahun' => $tahun, 'nomor_urut' => $urut, 'nomor_sk' => SuratKeputusan::composeNumber($urut, $tahun), 'tanggal_surat' => "{$tahun}-02-01", 'perihal' => 'Uji', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji']);
    }

    public function test_preview_shows_the_next_number_of_this_year(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Nomor yang akan diberikan')
            ->assertSeeHtml('data-sk-preview="7 TAHUN ' . $year . '"')
            ->assertSee("Nomor urut 7 tahun {$year}.");
    }

    public function test_preview_follows_the_year_of_the_chosen_date(): void
    {
        $year = now()->year;
        $this->sk($year - 1, 41);

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData(['tanggal_surat' => ($year - 1) . '-12-30'])
            ->assertSeeHtml('data-sk-preview="42 TAHUN ' . ($year - 1) . '"')
            ->setActionData(['tanggal_surat' => ($year + 1) . '-01-02'])
            ->assertSeeHtml('data-sk-preview="1 TAHUN ' . ($year + 1) . '"');
    }

    public function test_preview_warns_about_a_date_outside_this_year(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertDontSeeHtml('data-sk-preview-other-year')
            ->setActionData(['tanggal_surat' => ($year - 1) . '-12-30'])
            ->assertSeeHtml('data-sk-preview-other-year="' . ($year - 1) . '"')
            ->assertSee('Tahun ' . ($year - 1) . ' sudah lewat: nomor ini menyambung register SK tahun ' . ($year - 1) . '.')
            ->setActionData(['tanggal_surat' => ($year + 1) . '-01-02'])
            ->assertSee('Tahun ' . ($year + 1) . ' belum berjalan: nomor ini membuka register SK tahun ' . ($year + 1) . '.')
            ->setActionData(['tanggal_surat' => "{$year}-03-01"])
            ->assertDontSeeHtml('data-sk-preview-other-year');
    }

    public function test_preview_matches_the_number_actually_given(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSeeHtml('data-sk-preview="7 TAHUN ' . $year . '"')
            ->setActionData(['jenis_sk' => 'Keuangan dan Operasional', 'perihal' => 'Tim BOS', 'nama_peminta' => 'Bendahara'])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertNotified('Nomor SK diberikan');

        $this->assertSame("7 TAHUN {$year}", SuratKeputusan::latest('id')->value('nomor_sk'));
    }

    public function test_preview_moves_on_when_someone_else_took_the_number(): void
    {
        $year = now()->year;
        $form = Livewire::test(ListSuratKeputusan::class)->mountAction('reserve')
            ->assertSeeHtml('data-sk-preview="7 TAHUN ' . $year . '"');

        $this->sk($year, 7);

        $form->setActionData(['perihal' => 'Diketik ulang'])
            ->assertSeeHtml('data-sk-preview="8 TAHUN ' . $year . '"');
    }

    public function test_preview_warns_when_the_year_is_used_up(): void
    {
        $year = now()->year;
        $this->sk($year, 9999);

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSeeHtml('data-sk-preview="habis"')
            ->assertSee("Nomor SK tahun {$year} sudah mencapai 9999");
    }
}
