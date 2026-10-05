<?php

namespace Tests\Feature;

use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\ComplaintResource\Pages\ListComplaints;
use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use App\Support\ComplaintNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Whistleblowing and confidential reports are flagged in the staff panel. */
class ConfidentialComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_secret_reports_are_flagged_and_ordinary_ones_are_not(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());

        $wbs = Complaint::where('complaint_type', 'whistleblowing')->firstOrFail();
        $plain = Complaint::where('complaint_type', 'complaint')->firstOrFail();
        $plain->update(['is_confidential' => false]);

        $this->assertTrue($wbs->isSecret());
        $this->assertFalse($plain->fresh()->isSecret());

        $this->get(ComplaintResource::getUrl('view', ['record' => $wbs]))->assertOk()->assertSee('Laporan rahasia');
        $this->get(ComplaintResource::getUrl('view', ['record' => $plain]))->assertOk()->assertDontSee('Laporan rahasia');

        Livewire::test(ListComplaints::class, ['activeTab' => 'whistleblowing'])->assertSee('Rahasia');
    }

    public function test_every_stage_of_a_report_is_recorded(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $handler = User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail();
        $complaint = Complaint::create(['complaint_type' => 'complaint', 'title' => 'Antrean lama', 'description' => 'Menunggu 2 jam', 'reporter_name' => 'Warga', 'status' => 'submitted', 'priority' => 'normal']);

        $this->assertSame(['submitted'], $complaint->statusLogs()->pluck('to_status')->all());

        $service = app(ComplaintService::class);
        $service->followUp($complaint, ['status' => 'in_progress', 'priority' => 'high', 'resolution_notes' => 'Cek CCTV'], $handler);
        $service->followUp($complaint->fresh(), ['status' => 'in_progress', 'priority' => 'high'], $handler); // nothing changed
        $service->followUp($complaint->fresh(), ['status' => 'resolved', 'priority' => 'high', 'response' => 'Sudah kami tambah petugas loket.'], $handler);

        $logs = $complaint->statusLogs()->get();
        $this->assertSame(['submitted', 'in_progress', 'resolved'], $logs->pluck('to_status')->all());
        $this->assertSame('Cek CCTV', $logs[1]->internal_note);
        $this->assertSame('Sudah kami tambah petugas loket.', $logs[2]->response);
        $this->assertSame($handler->id, $logs[2]->actor_id);

        $this->expectException(\LogicException::class);
        $logs[0]->update(['to_status' => 'closed']);
    }

    public function test_report_numbers_are_sequential_per_type_and_month(): void
    {
        $this->freezeTime();
        $period = now()->format('Ym');
        // An older report already holds number 41 this month (e.g. issued before the counter existed).
        DB::table('complaints')->insert(['complaint_number' => "PEM-{$period}-0041", 'complaint_type' => 'complaint', 'title' => 'Lama', 'description' => 'x', 'status' => 'closed', 'priority' => 'normal', 'created_at' => now(), 'updated_at' => now()]);

        $this->assertSame("PEM-{$period}-0042", ComplaintNumber::next('complaint'));
        $this->assertSame("PEM-{$period}-0043", ComplaintNumber::next('pengaduan'));
        $this->assertSame("WSB-{$period}-0001", ComplaintNumber::next('whistleblowing'));
        $this->assertSame("SRN-{$period}-0001", ComplaintNumber::next('suggestion'));

        $this->travel(1)->months();
        $this->assertSame('PEM-' . now()->format('Ym') . '-0001', ComplaintNumber::next('complaint'));
    }

    public function test_reports_are_sent_through_the_public_api(): void
    {
        Storage::fake('local');

        $number = $this->post('/api/publik/pengaduan', [
            'jenis' => 'pengaduan', 'reporter_name' => 'Warga', 'reporter_email' => 'warga@example.test',
            'complaint_title' => 'Antrean', 'complaint_description' => 'Lama sekali',
            'attachment' => UploadedFile::fake()->image('bukti.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()->json('nomor');
        $complaint = Complaint::where('complaint_number', $number)->firstOrFail();
        $this->assertStringStartsWith('PEM-', $number);
        Storage::disk('local')->assertExists(ComplaintService::evidence($complaint)[0]);

        $wbs = $this->postJson('/api/publik/pengaduan', ['jenis' => 'whistleblowing', 'complaint_title' => 'Pungli', 'complaint_description' => 'x', 'anonymous' => true, 'reporter_email' => 'jangan@disimpan.test'])
            ->assertCreated()->json('nomor');
        $this->assertNull(Complaint::where('complaint_number', $wbs)->value('reporter_email'));

        $this->postJson('/api/publik/pengaduan', ['jenis' => 'saran', 'suggestion' => 'Tambah kursi'])->assertCreated()->assertJsonPath('nomor', null);
        $this->postJson('/api/publik/pengaduan', ['jenis' => 'pengaduan'])->assertJsonValidationErrors(['reporter_name', 'complaint_title']);
    }

    public function test_reporters_track_their_report_by_number_and_email(): void
    {
        $this->seed();
        $complaint = app(ComplaintService::class)->submit('complaint', ['reporter_name' => 'Warga', 'reporter_email' => 'warga@example.test', 'complaint_title' => 'Antrean', 'complaint_description' => 'x']);
        app(ComplaintService::class)->followUp($complaint, ['status' => 'resolved', 'priority' => 'normal', 'response' => 'Sudah ditangani.', 'resolution_notes' => 'Rahasia internal'], User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
        $url = '/api/publik/pengaduan/' . $complaint->complaint_number;

        $this->getJson($url)->assertJsonValidationErrors('email');
        $this->getJson($url . '?email=orang@lain.test')->assertNotFound();

        $tracked = $this->getJson($url . '?email=warga@example.test')
            ->assertOk()
            ->assertJsonPath('status', 'resolved')
            ->assertJsonPath('tahapan.1.tanggapan', 'Sudah ditangani.');
        $this->assertStringNotContainsString('Rahasia internal', $tracked->getContent());
    }

    public function test_handlers_list_reports_with_filters(): void
    {
        $this->seed();
        $service = app(ComplaintService::class);
        $wbs = $service->submit('whistleblowing', ['complaint_title' => 'Pungli', 'complaint_description' => 'x', 'anonymous' => true, 'reporter_name' => 'Rahasia']);
        $plain = $service->submit('complaint', ['reporter_name' => 'Warga', 'reporter_email' => 'w@example.test', 'complaint_title' => 'AC rusak', 'complaint_description' => 'x']);

        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
        $rows = collect($this->getJson('/api/pengaduan?jenis=whistleblowing&per_halaman=100')->assertOk()->json('data'));
        $this->assertTrue($rows->pluck('nomor')->contains($wbs->complaint_number));
        $this->assertFalse($rows->pluck('nomor')->contains($plain->complaint_number));
        $this->assertSame('Anonim', $rows->firstWhere('nomor', $wbs->complaint_number)['pelapor']);
        $this->assertTrue($rows->firstWhere('nomor', $wbs->complaint_number)['rahasia']);

        $this->getJson('/api/pengaduan?q=AC rusak')->assertJsonPath('data.0.nomor', $plain->complaint_number);

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/pengaduan')->assertForbidden();
    }

    public function test_handlers_follow_up_through_the_api(): void
    {
        $this->seed();
        $complaint = app(ComplaintService::class)->submit('complaint', ['reporter_name' => 'Warga', 'reporter_email' => 'w@example.test', 'complaint_title' => 'Toilet', 'complaint_description' => 'x']);
        $handler = User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail();
        Sanctum::actingAs($handler);
        $url = '/api/pengaduan/' . $complaint->id;

        $this->patchJson($url, ['status' => 'resolved'])->assertUnprocessable()->assertJsonPath('message', 'Isi tanggapan untuk pelapor sebelum menyelesaikan laporan.');

        $this->patchJson($url, ['status' => 'in_progress', 'prioritas' => 'high', 'penangan' => $handler->id, 'catatan_internal' => 'Panggil teknisi'])
            ->assertOk()->assertJsonPath('status', 'in_progress')->assertJsonPath('prioritas', 'high');
        $this->patchJson($url, ['status' => 'resolved', 'tanggapan' => 'Sudah diperbaiki.'])
            ->assertOk()->assertJsonPath('tanggapan', 'Sudah diperbaiki.')->assertJsonPath('tahapan.2.oleh', $handler->name);

        $this->patchJson($url, ['status' => 'closed', 'penangan' => User::where('email', 'staff1@mtsn2malang.sch.id')->value('id')])->assertJsonValidationErrors('penangan');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->patchJson($url, ['status' => 'closed'])->assertForbidden();
    }
}
