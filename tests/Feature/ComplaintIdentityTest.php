<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\ComplaintResource\Pages\ViewComplaint;
use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Rahasiakan identitas: a handler hides a reporter's identity after the report came in. */
class ComplaintIdentityTest extends TestCase
{
    use RefreshDatabase;

    private User $handler;

    private Complaint $complaint;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->handler = User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail();
        $this->complaint = Complaint::create(['complaint_type' => 'complaint', 'title' => 'Pungutan', 'description' => 'Diminta biaya tambahan', 'reporter_name' => 'Warga', 'reporter_email' => 'warga@example.test', 'status' => 'submitted', 'priority' => 'normal']);
    }

    public function test_handler_hides_the_reporters_identity_with_a_reason(): void
    {
        $this->actingAs($this->handler);

        Livewire::test(ViewComplaint::class, ['record' => $this->complaint->getRouteKey()])
            ->assertActionVisible('makeConfidential')
            ->callAction('makeConfidential', ['reason' => ''])
            ->assertHasActionErrors(['reason' => 'required'])
            ->callAction('makeConfidential', ['reason' => 'Menyangkut oknum petugas'])
            ->assertHasNoActionErrors()
            ->assertNotified('Identitas pelapor dirahasiakan.')
            ->assertActionHidden('makeConfidential');

        $this->assertTrue($this->complaint->fresh()->isSecret());
        $this->assertSame('Identitas pelapor dirahasiakan: Menyangkut oknum petugas', $this->complaint->statusLogs()->reorder('id', 'desc')->value('internal_note'));
    }

    public function test_hidden_identity_cannot_be_hidden_twice(): void
    {
        $service = app(ComplaintService::class);
        $service->makeConfidential($this->complaint, $this->handler, 'Sensitif');

        $this->expectException(TicketActionException::class);
        $service->makeConfidential($this->complaint->fresh(), $this->handler, 'Lagi');
    }

    public function test_identity_is_hidden_through_the_api(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->handler);
        $url = '/api/pengaduan/' . $this->complaint->id . '/rahasiakan';

        $this->postJson($url, [])->assertJsonValidationErrors('alasan');
        $this->postJson($url, ['alasan' => 'Menyangkut oknum'])->assertOk()->assertJsonPath('rahasia', true);
        $this->postJson($url, ['alasan' => 'Lagi'])->assertUnprocessable();

        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson($url, ['alasan' => 'x'])->assertForbidden();
    }
}
