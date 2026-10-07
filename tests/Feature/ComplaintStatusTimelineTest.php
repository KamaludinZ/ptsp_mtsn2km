<?php

namespace Tests\Feature;

use App\Filament\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Linimasa riwayat status di halaman pengaduan (petugas pengawasan). */
class ComplaintStatusTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_see_the_status_history_in_order(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $handler = User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail();
        $complaint = Complaint::query()->firstOrFail();

        ComplaintStatusLog::create(['complaint_id' => $complaint->id, 'from_status' => 'submitted', 'to_status' => 'in_review', 'actor_id' => $handler->id, 'response' => 'Laporan sedang kami telaah.', 'internal_note' => 'Cek CCTV loket']);

        $this->actingAs($handler)
            ->get(ComplaintResource::getUrl('view', ['record' => $complaint]))
            ->assertOk()
            ->assertSee('Riwayat status')
            ->assertSee('data-complaint-timeline', false)
            ->assertSeeInOrder(['Riwayat status', 'Ditelaah', 'Saat ini', $handler->name, 'Laporan sedang kami telaah.', 'Cek CCTV loket']);
    }

    public function test_status_history_cannot_be_changed_or_removed(): void
    {
        $complaint = Complaint::create(['complaint_type' => 'complaint', 'title' => 'Uji', 'description' => 'Uraian uji', 'status' => 'submitted', 'reporter_name' => 'Pelapor']);
        $log = ComplaintStatusLog::create(['complaint_id' => $complaint->id, 'from_status' => 'submitted', 'to_status' => 'in_review', 'response' => 'Ditelaah']);

        try {
            $log->update(['response' => 'Diubah']);
            $this->fail('The model should refuse the change.');
        } catch (\LogicException) {
        }

        foreach ([
            fn () => \Illuminate\Support\Facades\DB::table('complaint_status_logs')->where('id', $log->id)->update(['response' => 'Diubah']),
            fn () => \Illuminate\Support\Facades\DB::table('complaint_status_logs')->where('id', $log->id)->delete(),
        ] as $change) {
            try {
                \Illuminate\Support\Facades\DB::transaction($change);
                $this->fail('The database should refuse the change.');
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertStringContainsString('Riwayat pengaduan tidak dapat diubah atau dihapus', $e->getMessage());
            }
        }

        $this->assertSame('Ditelaah', $log->fresh()->response);
    }
}
