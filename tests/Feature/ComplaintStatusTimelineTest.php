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
}
