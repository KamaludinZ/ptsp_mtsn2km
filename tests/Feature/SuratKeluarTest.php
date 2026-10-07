<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Exports\SuratKeluarExport;
use App\Filament\Resources\SuratKeluarResource;
use App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar;
use App\Filament\Resources\SuratKeluarResource\Pages\ViewSuratKeluar;
use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\SuratKeluarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
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

    public function test_policy_lets_every_processor_unit_keep_the_register(): void
    {
        $letter = SuratKeluar::firstOrFail();
        $wakaHumas = tap(User::factory()->create(['user_type' => 'pegawai']))->assignRole('waka_humas');
        $inactive = tap(User::factory()->create(['user_type' => 'pegawai', 'is_active' => false]))->assignRole('tata_usaha');
        $frontDesk = $this->user('loket1@mtsn2malang.sch.id');

        $this->assertTrue($wakaHumas->can('viewAny', SuratKeluar::class));
        $this->assertTrue($wakaHumas->can('create', SuratKeluar::class));
        $this->assertTrue($wakaHumas->can('update', $letter));
        $this->assertFalse($wakaHumas->can('delete', $letter));
        $this->assertFalse($inactive->can('viewAny', SuratKeluar::class));
        $this->assertFalse($frontDesk->can('view', $letter));

        Sanctum::actingAs($frontDesk);
        $this->getJson('/api/surat-keluar')->assertForbidden();
        $this->putJson('/api/surat-keluar/' . $letter->id, ['perihal' => 'x'])->assertForbidden();
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

    public function test_number_requests_are_listed_with_their_range_and_progress(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $letters = app(SuratKeluarService::class)->reserve(3, now(), auth()->user());
        $letters->first()->update(['perihal' => 'Undangan rapat komite', 'tujuan_surat' => 'Komite Madrasah']);
        $letters->last()->update(['perihal' => 'Belum ada tujuan']); // still a draft
        $batch = \App\Models\SuratKeluarBatch::latest('id')->firstOrFail();

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Widgets\NumberRequestHistory::class)
            ->assertCanSeeTableRecords([$batch])
            ->assertTableColumnStateSet('range', $batch->nomor_awal . '–' . $batch->nomor_akhir, $batch)
            ->assertSee('1 dari 3')
            ->assertTableColumnStateSet('creator.name', auth()->user()->name, $batch);

        $this->get(ListSuratKeluar::getUrl())->assertOk()->assertSee('Riwayat permintaan nomor agenda');
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

    public function test_new_letter_is_recorded_complete_from_the_request_form(): void
    {
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        $this->actingAs($staff);

        Livewire::test(ListSuratKeluar::class)
            ->callAction('reserve', [
                'count' => 1,
                'tanggal_surat' => now()->toDateString(),
                'tujuan_surat' => 'Kepala Kankemenag Kota Malang',
                'perihal' => 'Laporan kegiatan',
                'jenis_surat' => 'Laporan',
                'klasifikasi' => 'OT.00',
                'lampiran' => '1 berkas',
                'tembusan' => ['Kepala Madrasah', 'Arsip'],
                'keterangan' => 'Dikirim via pos',
            ])
            ->assertHasNoActionErrors();

        $letter = SuratKeluar::latest('id')->firstOrFail();
        $this->assertFalse($letter->isDraft());
        $this->assertSame('1 berkas', $letter->lampiran);
        $this->assertSame("Kepala Madrasah\nArsip", $letter->tembusan);
        $this->assertSame('Dikirim via pos', $letter->keterangan);
        $this->assertSame($staff->id, $letter->pembuat_id);
    }

    public function test_letter_detail_page_shows_the_letter_and_its_attachments(): void
    {
        Storage::fake('local');
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $letter = app(SuratKeluarService::class)->reserve(1, now(), auth()->user(), [
            'perihal' => 'Undangan rapat komite',
            'tujuan_surat' => 'Ketua Komite Madrasah',
            'lampiran' => '1 berkas',
            'tembusan' => "Kepala Madrasah\nArsip",
        ])->first();

        $this->get(SuratKeluarResource::getUrl('view', ['record' => $letter]))
            ->assertOk()
            ->assertSee($letter->nomor_surat)
            ->assertSee('Undangan rapat komite')
            ->assertSee('Belum ada berkas diunggah.');

        Livewire::test(ViewSuratKeluar::class, ['record' => $letter->getRouteKey()])
            ->callAction('edit', [
                'berkas_lampiran' => [UploadedFile::fake()->create('daftar-hadir.pdf', 50, 'application/pdf')],
            ])
            ->assertHasNoActionErrors();

        $letter->refresh();
        $this->assertCount(1, $letter->berkas_lampiran);
        $path = $letter->berkas_lampiran[0];
        Storage::disk('local')->assertExists($path);
        $this->assertSame('daftar-hadir.pdf', $letter->attachmentName($path));

        $this->get(SuratKeluarResource::getUrl('view', ['record' => $letter]))
            ->assertSee('daftar-hadir.pdf')
            ->assertSee(route('surat-keluar.lampiran', [$letter, 0]), false);
        $this->get(route('surat-keluar.lampiran', [$letter, 0]))->assertOk()->assertHeader('content-disposition', 'inline; filename=daftar-hadir.pdf');
        $this->get(route('surat-keluar.lampiran', [$letter, 0, 'unduh' => 1]))->assertDownload('daftar-hadir.pdf');
        Livewire::test(ListSuratKeluar::class)->assertTableActionVisible('lampiran', $letter);
        $this->get(route('surat-keluar.lampiran', [$letter, 1]))->assertNotFound();

        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'))
            ->get(route('surat-keluar.lampiran', [$letter, 0]))
            ->assertForbidden();
    }

    public function test_attachment_is_uploaded_while_recording_a_single_letter(): void
    {
        Storage::fake('local');
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeluar::class)
            ->callAction('reserve', [
                'count' => 1,
                'tanggal_surat' => now()->toDateString(),
                'perihal' => 'Undangan rapat',
                'tujuan_surat' => 'Orang tua/wali siswa',
                'berkas_lampiran' => [UploadedFile::fake()->create('jadwal.pdf', 20, 'application/pdf')],
            ])
            ->assertHasNoActionErrors();

        $letter = SuratKeluar::latest('id')->firstOrFail();
        $this->assertCount(1, $letter->berkas_lampiran);
        $this->assertSame('jadwal.pdf', $letter->attachmentName($letter->berkas_lampiran[0]));

        $batch = app(SuratKeluarService::class)->reserve(2, now(), auth()->user(), ['berkas_lampiran' => ['surat-keluar/x.pdf']]);
        $this->assertTrue($batch->every(fn (SuratKeluar $l) => $l->berkas_lampiran === null));
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

    public function test_numbers_can_be_reserved_through_the_api(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $next = app(SuratKeluarService::class)->nextNumber(now()->year);

        $this->postJson('/api/surat-keluar/nomor', ['jumlah' => 3, 'perihal' => 'Undangan rapat', 'tembusan' => ['Kepala Madrasah', 'Arsip'], 'klasifikasi' => 'PP.00'])
            ->assertCreated()
            ->assertJsonPath('jumlah', 3)
            ->assertJsonPath('data.0.nomor_urut', $next)
            ->assertJsonPath('data.2.nomor_urut', $next + 2)
            ->assertJsonPath('data.0.tembusan', ['Kepala Madrasah', 'Arsip'])
            ->assertJsonPath('data.0.lengkap', false);

        $this->postJson('/api/surat-keluar/nomor', ['jumlah' => 0])->assertUnprocessable()->assertJsonValidationErrors('jumlah');

        DB::table('surat_sequences')->where('tahun', now()->year)->update(['last_number' => 9999]);
        $this->postJson('/api/surat-keluar/nomor', ['jumlah' => 1])->assertUnprocessable()->assertJsonPath('message', 'Nomor surat tahun ' . now()->year . ' tinggal 0.');

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'));
        $this->postJson('/api/surat-keluar/nomor', ['jumlah' => 1])->assertForbidden();
    }

    public function test_letter_data_is_saved_through_the_api(): void
    {
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        $letter = app(SuratKeluarService::class)->reserve(1, now()->setMonth(2), $staff)->first();
        Sanctum::actingAs($staff);

        $this->putJson('/api/surat-keluar/' . $letter->id, [
            'tanggal_surat' => now()->setMonth(5)->toDateString(),
            'tujuan_surat' => 'Kepala Dinas Pendidikan Kota Malang',
            'perihal' => 'Permohonan data',
            'klasifikasi' => 'KS.00',
            'tembusan' => ['Arsip'],
        ])
            ->assertOk()
            ->assertJsonPath('nomor_surat', 'B-' . $letter->nomor_urut . '/MTsN2KM/KS.00/05/' . now()->year)
            ->assertJsonPath('lengkap', true);

        $this->putJson('/api/surat-keluar/' . $letter->id, ['tujuan_surat' => 'x', 'perihal' => 'y', 'tanggal_surat' => now()->addYear()->toDateString()])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Tanggal surat harus di tahun ' . now()->year . ', sesuai nomornya.');

        $this->putJson('/api/surat-keluar/' . $letter->id, ['perihal' => 'tanpa tujuan'])->assertJsonValidationErrors('tujuan_surat');
    }

    public function test_register_is_listed_and_exported_through_the_api(): void
    {
        Excel::fake();
        $this->freezeTime();
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        app(SuratKeluarService::class)->reserve(1, now(), $staff, ['perihal' => 'Undangan wisuda 100%', 'tujuan_surat' => 'Wali murid', 'jenis_surat' => 'Undangan']);
        Sanctum::actingAs($staff);

        $this->getJson('/api/surat-keluar?q=' . urlencode('wisuda 100%'))->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.perihal', 'Undangan wisuda 100%');
        $this->getJson('/api/surat-keluar?jenis_surat=Undangan&lengkap=1')->assertOk()->assertJsonPath('data.0.jenis_surat', 'Undangan');
        $this->getJson('/api/surat-keluar?tahun=' . (now()->year + 5))->assertOk()->assertJsonPath('total', 0);

        $this->get('/api/surat-keluar/ekspor')->assertOk();
        Excel::assertDownloaded('register-surat-keluar-' . now()->format('Ymd-His') . '.xlsx');

        $this->get('/api/surat-keluar/ekspor?format=pdf')->assertOk()->assertHeader('content-type', 'application/pdf');

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'));
        $this->getJson('/api/surat-keluar')->assertForbidden();
    }
}
