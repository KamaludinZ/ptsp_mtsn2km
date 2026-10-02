<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Exports\SuratKeluarExport;
use App\Filament\Resources\SuratKeluarResource;
use App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar;
use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\SuratKeluarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Surat keluar: the outgoing-letter register kept by Tata Usaha. */
class SuratKeluarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_back_office_sees_this_years_register(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))
            ->get(SuratKeluarResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Surat Keluar');

        $letters = SuratKeluar::where('tahun', now()->year)->get();
        $this->assertNotEmpty($letters);

        Livewire::test(ListSuratKeluar::class)
            ->assertCanSeeTableRecords($letters)
            ->assertSee($letters->first()->nomor_surat);
    }

    public function test_front_desk_cannot_open_the_register(): void
    {
        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'))
            ->get(SuratKeluarResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_letters_are_never_deleted(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->assertFalse(SuratKeluarResource::canDelete(SuratKeluar::firstOrFail()));
        $this->assertFalse(SuratKeluarResource::canDeleteAny());
    }

    public function test_reserving_three_numbers_at_once_gives_consecutive_numbers(): void
    {
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        $service = app(SuratKeluarService::class);
        $next = $service->nextNumber(now()->year);

        $letters = $service->reserve(3, now(), $staff);

        $this->assertSame([$next, $next + 1, $next + 2], $letters->pluck('nomor_urut')->all());
        $this->assertTrue($letters->every(fn (SuratKeluar $l) => $l->pembuat_id === $staff->id && $l->batch_id === $letters->first()->batch_id));
        $this->assertDatabaseHas('surat_keluar_batches', ['jumlah_diminta' => 3, 'nomor_awal' => $next, 'nomor_akhir' => $next + 2]);
        $this->assertSame($next + 3, $service->nextNumber(now()->year));
    }

    public function test_numbering_restarts_every_year(): void
    {
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        $nextYear = now()->addYear()->startOfYear();

        $letter = app(SuratKeluarService::class)->reserve(1, $nextYear, $staff)->first();

        $this->assertSame(1, $letter->nomor_urut);
        $this->assertSame((int) $nextYear->format('Y'), $letter->tahun);
        $this->assertStringStartsWith('B-1/', $letter->nomor_surat);
        $this->assertStringEndsWith('/01/' . $nextYear->format('Y'), $letter->nomor_surat);
    }

    public function test_numbers_stop_at_9999(): void
    {
        DB::table('surat_sequences')->updateOrInsert(['tahun' => now()->year], ['last_number' => 9998]);

        $this->expectException(TicketActionException::class);
        app(SuratKeluarService::class)->reserve(2, now(), $this->user('staff1@mtsn2malang.sch.id'));
    }

    public function test_officer_requests_numbers_from_the_register_page(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $before = SuratKeluar::count();

        Livewire::test(ListSuratKeluar::class)
            ->callAction('reserve', ['count' => 0, 'tanggal_surat' => now()->toDateString()])
            ->assertHasActionErrors(['count']);

        Livewire::test(ListSuratKeluar::class)
            ->callAction('reserve', ['count' => 3, 'tanggal_surat' => now()->toDateString()])
            ->assertHasNoActionErrors()
            ->assertNotified('3 nomor surat diberikan');

        $this->assertSame($before + 3, SuratKeluar::count());
    }

    public function test_officer_completes_a_reserved_letter(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $letter = app(SuratKeluarService::class)->reserve(1, now()->setMonth(3), auth()->user())->first();
        $this->assertTrue($letter->isDraft());

        Livewire::test(ListSuratKeluar::class)
            ->callTableAction('edit', $letter, [
                'tanggal_surat' => now()->setMonth(4)->toDateString(),
                'tujuan_surat' => 'Kepala Dinas Pendidikan Kota Malang',
                'perihal' => 'Permohonan izin kegiatan',
                'jenis_surat' => 'Surat Dinas',
                'klasifikasi' => 'PP.00',
                'tembusan' => ['Kepala Madrasah', 'Arsip'],
            ])
            ->assertHasNoTableActionErrors();

        $letter->refresh();
        $this->assertFalse($letter->isDraft());
        $this->assertSame('Permohonan izin kegiatan', $letter->perihal);
        $this->assertSame("Kepala Madrasah\nArsip", $letter->tembusan);
        $this->assertSame('B-' . $letter->nomor_urut . '/MTsN2KM/PP.00/04/' . now()->year, $letter->nomor_surat);
    }

    public function test_reserving_with_letter_data_fills_every_number(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeluar::class)
            ->callAction('reserve', ['count' => 2, 'tanggal_surat' => now()->toDateString(), 'perihal' => 'Undangan rapat', 'tujuan_surat' => 'Orang tua/wali siswa', 'klasifikasi' => 'KS.00'])
            ->assertHasNoActionErrors();

        $letters = SuratKeluar::latest('id')->take(2)->get();
        $this->assertTrue($letters->every(fn (SuratKeluar $l) => $l->perihal === 'Undangan rapat' && str_contains($l->nomor_surat, '/KS.00/')));
    }

    public function test_register_can_be_searched_and_filtered(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $service = app(SuratKeluarService::class);
        $invite = $service->reserve(1, now()->startOfYear()->addDays(40), auth()->user(), ['perihal' => 'Undangan wisuda', 'tujuan_surat' => 'Orang tua/wali siswa', 'jenis_surat' => 'Undangan', 'klasifikasi' => 'KS.00'])->first();
        $report = $service->reserve(1, now()->startOfYear()->addDays(100), auth()->user(), ['perihal' => 'Laporan bulanan', 'tujuan_surat' => 'Kankemenag', 'jenis_surat' => 'Laporan', 'klasifikasi' => 'OT.00'])->first();

        Livewire::test(ListSuratKeluar::class)->searchTable('wisuda')
            ->assertCanSeeTableRecords([$invite])->assertCanNotSeeTableRecords([$report]);
        Livewire::test(ListSuratKeluar::class)->filterTable('jenis_surat', 'Laporan')
            ->assertCanSeeTableRecords([$report])->assertCanNotSeeTableRecords([$invite]);
        Livewire::test(ListSuratKeluar::class)->filterTable('klasifikasi', 'KS.00')
            ->assertCanSeeTableRecords([$invite])->assertCanNotSeeTableRecords([$report]);
        Livewire::test(ListSuratKeluar::class)
            ->filterTable('tanggal', ['from' => now()->startOfYear()->addDays(90)->toDateString(), 'until' => null])
            ->assertCanSeeTableRecords([$report])->assertCanNotSeeTableRecords([$invite]);
    }

    public function test_register_downloads_as_excel_and_pdf(): void
    {
        Excel::fake();
        $this->freezeTime();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeluar::class)->callAction('excel');
        Excel::assertDownloaded('register-surat-keluar-' . now()->format('Ymd-His') . '.xlsx', fn (SuratKeluarExport $export) => $export->query()->count() === SuratKeluar::where('tahun', now()->year)->count());

        Livewire::test(ListSuratKeluar::class)
            ->callAction('pdf')
            ->assertFileDownloaded('register-surat-keluar-' . now()->format('Ymd-His') . '.pdf');
    }
}
