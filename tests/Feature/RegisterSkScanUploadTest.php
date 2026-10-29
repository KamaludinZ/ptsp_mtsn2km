<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Unggah Scan SK: in Minta Nomor SK or later through Ubah, kept on the private disk. */
class RegisterSkScanUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    private function data(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepegawaian dan Pengangkatan',
            'perihal' => 'Pengangkatan pembina ekstrakurikuler',
            'nama_peminta' => 'Waka Kesiswaan',
            ...$override,
        ];
    }

    public function test_scan_can_be_uploaded_while_asking_for_the_number(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Scan SK (opsional)')
            ->assertSee('PDF atau gambar (JPG/PNG), maks. 10 MB.')
            ->setActionData($this->data(['berkas_scan' => UploadedFile::fake()->create('SK pembina.pdf', 300, 'application/pdf')]))
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $sk = SuratKeputusan::latest('id')->firstOrFail();
        $this->assertStringStartsWith('surat-keputusan/', $sk->berkas_scan);
        $this->assertSame('SK pembina.pdf', $sk->berkas_scan_nama);
        $this->assertSame('SK pembina.pdf', $sk->scanName());
        $this->assertSame('terarsip', $sk->status());
        Storage::disk('local')->assertExists($sk->berkas_scan);
        // Private disk only: nothing under the public disk.
        $this->assertFalse(Storage::disk('public')->exists($sk->berkas_scan));
    }

    public function test_number_can_be_taken_without_a_scan(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data())
            ->assertHasNoActionErrors();

        $sk = SuratKeputusan::latest('id')->firstOrFail();
        $this->assertNull($sk->berkas_scan);
        $this->assertNull($sk->scanName());
        $this->assertSame('menunggu_scan', $sk->status());
    }

    public function test_scan_can_follow_later_through_ubah(): void
    {
        $sk = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['berkas_scan' => UploadedFile::fake()->image('scan-sk-1.jpg')])
            ->assertHasNoTableActionErrors();

        $sk->refresh();
        $this->assertSame('scan-sk-1.jpg', $sk->berkas_scan_nama);
        $this->assertSame('terarsip', $sk->status());
        Storage::disk('local')->assertExists($sk->berkas_scan);
    }

    public function test_removing_the_scan_through_ubah_returns_the_sk_to_waiting(): void
    {
        $sk = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();
        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['berkas_scan' => UploadedFile::fake()->create('SK 1.pdf', 120, 'application/pdf')])
            ->assertHasNoTableActionErrors();
        $this->assertSame('terarsip', $sk->fresh()->status());

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['berkas_scan' => null])
            ->assertHasNoTableActionErrors();

        $sk->refresh();
        $this->assertNull($sk->berkas_scan);
        $this->assertNull($sk->berkas_scan_nama);
        $this->assertSame('menunggu_scan', $sk->status());
    }

    public function test_only_pdf_and_images_up_to_10_mb_are_accepted(): void
    {
        $before = SuratKeputusan::count();

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['berkas_scan' => UploadedFile::fake()->create('sk.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')]))
            ->assertHasActionErrors(['berkas_scan']);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['berkas_scan' => UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf')]))
            ->assertHasActionErrors(['berkas_scan']);

        $this->assertSame($before, SuratKeputusan::count());
    }
}
