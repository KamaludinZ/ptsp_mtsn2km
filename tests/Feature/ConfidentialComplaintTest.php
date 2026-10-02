<?php

namespace Tests\Feature;

use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\ComplaintResource\Pages\ListComplaints;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
}
