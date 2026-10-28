<?php

namespace Tests\Feature;

use App\Filament\Pages\System\ActivityTrail;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Cetak / Unduh of the SK register leaves a trail: who downloaded which part of the register. */
class RegisterSkDownloadAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Excel::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function exports()
    {
        return Activity::inLog('surat_keputusan')->where('event', 'exported')->latest('id')->get();
    }

    public function test_a_download_is_logged_with_its_format_size_and_criteria(): void
    {
        $this->actingAs($this->officer);
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->assertSee('setiap unduhan dicatat di Log')
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->searchTable('panitia')
            ->callAction('excel')
            ->callAction('pdf');

        [$pdf, $excel] = $this->exports()->all();
        $this->assertSame("Mengunduh laporan register SK tahun {$year} (Excel, 2 SK)", $excel->description);
        $this->assertSame("Mengunduh laporan register SK tahun {$year} (PDF, 2 SK)", $pdf->description);
        $this->assertTrue($excel->causer->is($this->officer));
        $this->assertSame('back_office', $excel->properties['peran_aktif']);
        $this->assertSame(['Kepanitiaan dan Tim Pelaksana Kegiatan'], $excel->properties['saringan']['jenis_sk']['values']);
        $this->assertSame('panitia', $excel->properties['pencarian']);
    }

    public function test_a_refused_empty_download_leaves_no_trail(): void
    {
        $this->actingAs($this->officer);

        Livewire::test(ListSuratKeputusan::class)
            ->searchTable('tidak ada perihal seperti ini')
            ->callAction('excel');

        $this->assertCount(0, $this->exports());
    }

    public function test_the_activity_trail_shows_what_the_report_held(): void
    {
        $this->actingAs($this->officer);
        Livewire::test(ListSuratKeputusan::class)->callAction('excel');

        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        Livewire::test(ActivityTrail::class)
            ->call('setType', 'register_sk')
            ->assertSee('Mengunduh laporan register SK tahun ' . now()->year . ' (Excel, 6 SK)')
            ->call('showDetail', $this->exports()->first()->id)
            ->assertSeeInOrder(['Format', 'Excel', 'Jumlah SK', '6']);
    }

    public function test_a_report_printed_in_a_borrowed_account_names_the_administrator(): void
    {
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($admin)->post(route('ganti-akun.mulai', $this->officer), ['alasan' => 'Meninjau register SK petugas']);

        $html = view('pdf.surat-keputusan-register', ['sk' => SuratKeputusan::orderBy('nomor_urut')->get(), 'years' => (string) now()->year])->render();

        $this->assertStringContainsString('oleh ' . e($this->officer->name) . ' (ganti akun oleh ' . e($admin->name) . ')', $html);
    }
}
