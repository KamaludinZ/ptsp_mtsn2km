<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\TicketDocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** ?pratinjau=1 on the document routes: a real PDF is shown in the page, anything else downloads. */
class TicketDocumentPreviewTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->seed(TicketDocumentSeeder::class);
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->ticket = Ticket::where('status', 'completed')->whereHas('output')
            ->whereHas('files', fn ($files) => $files->where('file_name', 'Kartu Keluarga.jpg'))->oldest('id')->firstOrFail();
    }

    public function test_a_pdf_is_shown_inline_and_still_downloads_without_pratinjau(): void
    {
        $pdf = $this->ticket->files()->where('file_name', 'Surat Permohonan.pdf')->firstOrFail();
        $this->actingAs($this->officer);

        $preview = $this->get(route('documents.ticket-file', $pdf) . '?pratinjau=1')->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline;', $preview->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Surat Permohonan.pdf', $preview->headers->get('Content-Disposition'));

        $this->assertStringStartsWith('attachment;', $this->get(route('documents.ticket-file', $pdf))->assertOk()->headers->get('Content-Disposition'));

        $output = $this->get(route('documents.ticket-output', $this->ticket->output) . '?pratinjau=1')->assertOk();
        $this->assertStringStartsWith('inline;', $output->headers->get('Content-Disposition'));
    }

    public function test_other_files_and_fake_pdfs_download_instead(): void
    {
        $jpg = $this->ticket->files()->where('file_name', 'Kartu Keluarga.jpg')->firstOrFail();
        Storage::disk('local')->put('ticket-files/bukan-pdf.pdf', '<html><script>alert(1)</script></html>');
        app(TicketService::class)->attachFile($this->ticket, 'ticket-files/bukan-pdf.pdf', 'Bukan PDF.pdf', $this->officer);
        $fake = $this->ticket->files()->where('file_name', 'Bukan PDF.pdf')->firstOrFail();
        $this->actingAs($this->officer);

        foreach ([$jpg, $fake] as $file) {
            $response = $this->get(route('documents.ticket-file', $file) . '?pratinjau=1')->assertOk();
            $this->assertStringStartsWith('attachment;', $response->headers->get('Content-Disposition'), $file->file_name);
        }
    }

    public function test_the_preview_is_authorized_like_the_download(): void
    {
        $pdf = $this->ticket->files()->where('file_name', 'Surat Permohonan.pdf')->firstOrFail();
        $url = route('documents.ticket-file', $pdf) . '?pratinjau=1';

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['user_type' => 'umum']))->get($url)->assertForbidden();
        $this->actingAs($this->ticket->user)->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }
}
