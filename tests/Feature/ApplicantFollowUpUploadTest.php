<?php

namespace Tests\Feature;

use App\Filament\Portal\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Portal pemohon: unggah berkas susulan pada permohonan yang masih berjalan. */
class ApplicantFollowUpUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
    }

    private function applicantTicket(string $status): Ticket
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $ticket = Ticket::where('user_id', $applicant->id)->firstOrFail();
        $ticket->forceFill(['status' => $status])->saveQuietly();
        $this->actingAs($applicant);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('portal'));

        return $ticket->fresh();
    }

    public function test_applicant_adds_follow_up_documents_to_an_open_request(): void
    {
        $ticket = $this->applicantTicket('submitted');
        $before = $ticket->files()->count();

        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
            ->assertActionVisible('upload')
            ->callAction('upload', ['files' => [UploadedFile::fake()->create('kk.pdf', 120, 'application/pdf')]])
            ->assertHasNoActionErrors()
            ->assertNotified('Berkas ditambahkan');

        $this->assertSame($before + 1, $ticket->files()->count());
        $this->assertTrue($ticket->logs()->where('action', 'file_uploaded')->where('notes', 'like', '%Berkas susulan dari pemohon.')->exists());
    }

    public function test_no_uploads_once_the_request_is_closed(): void
    {
        $ticket = $this->applicantTicket('completed');

        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])->assertActionHidden('upload');
    }
}
