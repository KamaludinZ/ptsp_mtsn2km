<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\SkRegisterRecap;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** bulan and hari_sampai_terarsip: computed by the database, read alike by every report. */
class RegisterSkReportColumnsTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($this->officer);
    }

    private function sk(int $nomorUrut): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->firstOrFail();
    }

    public function test_bulan_follows_the_date_of_the_letter(): void
    {
        // Seeded: 1 Jan, 24 Jan, 16 Feb, 11 Mar, 3 Apr, 26 Apr.
        $this->assertSame([1, 1, 2, 3, 4, 4], SuratKeputusan::where('tahun', now()->year)->orderBy('nomor_urut')->pluck('bulan')->all());

        $sk = app(SuratKeputusanService::class)->describe($this->sk(2), ['tanggal_surat' => now()->year . '-09-15']);
        $this->assertSame(9, $sk->bulan);
    }

    public function test_days_to_the_archive_are_counted_once_the_scan_is_in(): void
    {
        $this->assertNull($this->sk(1)->hari_sampai_terarsip);

        $this->travel(3)->days();
        $sk = app(SuratKeputusanService::class)->attachScan($this->sk(1), UploadedFile::fake()->createWithContent('SK.pdf', '%PDF-1.4'));
        $this->assertEqualsWithDelta(3.0, $sk->hari_sampai_terarsip, 0.01);
        $this->assertEqualsWithDelta(3.0, SkRegisterRecap::build(now()->year)['rata_hari_arsip'], 0.05);

        $this->assertNull(app(SuratKeputusanService::class)->removeScan($sk)->hari_sampai_terarsip);
    }

    public function test_the_api_tells_the_days_to_the_archive(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $sk = $this->sk(1);
        $this->travel(2)->days();

        $this->postJson("/api/penomoran-sk/{$sk->id}/scan", ['berkas_scan' => UploadedFile::fake()->createWithContent('SK.pdf', '%PDF-1.4')])->assertOk()
            ->assertJsonPath('hari_sampai_terarsip', 2);
        $this->getJson("/api/penomoran-sk/{$this->sk(2)->id}")->assertOk()->assertJsonPath('hari_sampai_terarsip', null);
    }

    public function test_the_columns_cannot_be_written(): void
    {
        $this->expectException(QueryException::class);
        DB::table('surat_keputusan')->where('id', $this->sk(1)->id)->update(['bulan' => 12]);
    }
}
