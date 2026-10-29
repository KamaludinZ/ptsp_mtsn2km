<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar;
use App\Filament\Resources\SuratKeputusanResource;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Buku surat keluar points SK to their own register instead of an agenda number. */
class SuratKeluarSkNoticeTest extends TestCase
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

    public function test_the_list_says_sk_are_numbered_in_their_own_register(): void
    {
        Livewire::test(ListSuratKeluar::class)
            ->assertSee('Nomor Surat Keputusan (SK) dicatat terpisah di')
            ->assertSee(SuratKeputusanResource::getUrl('index'), false);
    }

    public function test_a_jenis_surat_that_is_an_sk_is_sent_to_the_sk_register(): void
    {
        $warning = 'Surat Keputusan bernomor sendiri ({N} TAHUN {YYYY}): minta nomornya di Register Penomoran SK, bukan di buku surat keluar.';

        Livewire::test(ListSuratKeluar::class)
            ->mountAction('reserve')
            ->setActionData(['jenis_surat' => 'Surat Keputusan'])
            ->assertSee($warning)
            ->setActionData(['jenis_surat' => 'SK Panitia PPDB'])
            ->assertSee($warning)
            ->setActionData(['jenis_surat' => 'Surat Keterangan'])
            ->assertDontSee($warning)
            ->assertSee('Format: ');
    }

    public function test_only_sk_names_are_taken_for_an_sk(): void
    {
        foreach (['Surat Keputusan', 'surat keputusan kepala', 'SK', 'sk tim'] as $jenis) {
            $this->assertTrue(Persuratan::isSuratKeputusan($jenis), $jenis);
        }
        foreach (['Surat Keterangan', 'SKCK', 'Surat Tugas', 'Risk', '', null] as $jenis) {
            $this->assertFalse(Persuratan::isSuratKeputusan($jenis), (string) $jenis);
        }
    }
}
