<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceHistoryResource\Pages\ListServiceHistory;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\TicketDocuments;
use Database\Seeders\TicketDocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Daftar dokumen of the ticket detail page: berkas persyaratan and hasil layanan, PDF or not. */
class TicketDocumentListTest extends TestCase
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
        $this->seed(TicketDocumentSeeder::class);
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function completedTicket(): Ticket
    {
        return Ticket::where('status', 'completed')->whereHas('output')->oldest('id')->firstOrFail();
    }

    public function test_the_demo_documents_are_pdf_and_non_pdf_files_on_the_private_disk(): void
    {
        $tickets = Ticket::with('files', 'output')->get();

        $this->assertTrue($tickets->every(fn (Ticket $ticket) => $ticket->files->contains('file_name', 'Surat Permohonan.pdf')));
        $this->assertTrue($tickets->contains(fn (Ticket $ticket) => $ticket->files->contains('file_name', 'Kartu Keluarga.jpg')));
        $this->assertTrue($tickets->where('status', 'completed')->every(fn (Ticket $ticket) => $ticket->output !== null));

        $file = $tickets->first()->files->firstWhere('file_name', 'Surat Permohonan.pdf');
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($file->file_path));
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($this->completedTicket()->output->file_path));

        // Seeding again adds nothing to tickets that already have documents.
        $count = \App\Models\TicketFile::count();
        $this->seed(TicketDocumentSeeder::class);
        $this->assertSame($count, \App\Models\TicketFile::count());
    }

    public function test_each_document_says_whether_it_is_a_pdf(): void
    {
        $ticket = Ticket::whereHas('files', fn ($files) => $files->where('file_name', 'Kartu Keluarga.jpg'))->oldest('id')->firstOrFail();
        $berkas = TicketDocuments::listFor($ticket, 'berkas');

        $this->assertSame(['Surat Permohonan.pdf', 'Kartu Keluarga.jpg'], array_column($berkas, 'nama'));
        $this->assertSame([true, false], array_column($berkas, 'pdf'));
        $this->assertSame(['PDF', 'JPG'], array_column($berkas, 'jenis'));
        $this->assertSame(route('documents.ticket-file', $ticket->files()->oldest('id')->first()), $berkas[0]['unduh']);
        $this->assertStringContainsString(' · ', $berkas[0]['keterangan']);

        $ticket = $this->completedTicket();
        $hasil = TicketDocuments::listFor($ticket, 'hasil');
        $this->assertCount(1, $hasil);
        $this->assertTrue($hasil[0]['pdf']);
        $this->assertSame("{$ticket->service->name} — {$ticket->ticket_number}", $hasil[0]['nama']);
        $this->assertSame(route('documents.ticket-output', $ticket->output), $hasil[0]['unduh']);
        $this->assertSame(route('documents.ticket-output', $ticket->output) . '?pratinjau=1', $hasil[0]['pratinjau']);

        $this->assertSame([], TicketDocuments::listFor(Ticket::whereDoesntHave('output')->firstOrFail(), 'hasil'));
        $this->assertTrue(TicketDocuments::isPdf('Scan.PDF'));
        $this->assertFalse(TicketDocuments::isPdf('scan.pdf.docx'));
        $this->assertFalse(TicketDocuments::isPdf(null));
    }

    public function test_the_ticket_detail_lists_berkas_and_hasil_layanan(): void
    {
        $ticket = $this->completedTicket();
        app(TicketService::class)->attachFile($ticket, 'ticket-files/formulir.docx', 'Formulir.docx', $this->officer);

        $this->actingAs($this->officer)->get(TicketResource::getUrl('view', ['record' => $ticket]))->assertOk()
            ->assertSeeInOrder(['Berkas', 'Surat Permohonan.pdf', 'PDF', 'Unduh', 'Formulir.docx', 'DOCX', 'Unduh', 'Hasil layanan', $ticket->service->name . ' — ' . $ticket->ticket_number, 'PDF', 'Unduh'])
            ->assertSee(route('documents.ticket-output', $ticket->output), false);

        $waiting = Ticket::whereDoesntHave('output')->where('status', '!=', 'completed')->firstOrFail();
        $this->get(TicketResource::getUrl('view', ['record' => $waiting]))->assertOk()->assertSee('Belum ada hasil layanan.');
    }

    public function test_a_pdf_berkas_opens_in_the_preview_modal_and_other_files_only_download(): void
    {
        $ticket = Ticket::whereHas('files', fn ($files) => $files->where('file_name', 'Kartu Keluarga.jpg'))->oldest('id')->firstOrFail();
        [$pdf, $image] = TicketDocuments::listFor($ticket, 'berkas');

        $this->assertSame(route('documents.ticket-file', [$ticket->files()->oldest('id')->first(), 'pratinjau' => 1]), $pdf['pratinjau']);
        $this->assertNull($image['pratinjau']);

        $html = $this->actingAs($this->officer)->get(TicketResource::getUrl('view', ['record' => $ticket]))->assertOk()
            ->assertSee('data-fi-modal-id="pratinjau-dokumen-berkas"', false)
            ->assertSee('data-pratinjau-dokumen', false)
            ->assertSee('Buka di tab baru')
            ->getContent();

        // The PDF has a Pratinjau button in Berkas and in Riwayat berkas; the JPG keeps only its Unduh links.
        $this->assertSame(2, substr_count($html, 'x-on:click="pratinjau = '));
        $this->assertSame(2, substr_count($html, 'pratinjau=1'));
    }

    public function test_the_riwayat_berkas_and_the_step_detail_preview_every_pdf(): void
    {
        $ticket = Ticket::whereHas('files', fn ($files) => $files->where('file_name', 'Kartu Keluarga.jpg'))->whereHas('output')->oldest('id')->firstOrFail();
        $pdf = $ticket->files()->where('file_name', 'Surat Permohonan.pdf')->firstOrFail();

        $html = $this->actingAs($this->officer)->get(TicketResource::getUrl('view', ['record' => $ticket]))->assertOk()
            ->assertSee('data-fi-modal-id="pratinjau-dokumen-riwayat"', false)
            ->getContent();
        $history = substr($html, strpos($html, 'Riwayat berkas &amp; hasil layanan'));
        $buttons = str_replace('\\', '', html_entity_decode($history));
        // The requirement PDF and the service output PDF (titled by its uploaded name); not the JPG.
        $this->assertSame(2, substr_count($history, 'x-on:click="pratinjau = '));
        $this->assertStringContainsString(route('documents.ticket-file', $pdf) . '?pratinjau=1', $buttons);
        $this->assertStringContainsString(route('documents.ticket-output', $ticket->output) . '?pratinjau=1', $buttons);
        $this->assertStringContainsString('u0022nama\u0022:\u0022Hasil ' . $ticket->service->name . '.pdf', html_entity_decode($history));

        // Riwayat Layanan: the detail of each upload step.
        $step = fn (string $notes) => Livewire::test(ListServiceHistory::class)
            ->mountTableAction('view', $ticket->logs()->where('notes', $notes)->firstOrFail())->html();

        $this->assertStringContainsString('data-fi-modal-id="pratinjau-dokumen-langkah"', $step('Berkas Surat Permohonan.pdf diunggah.'));
        $this->assertSame(1, substr_count($step('Berkas Surat Permohonan.pdf diunggah.'), 'x-on:click="pratinjau = '));
        $this->assertSame(0, substr_count($step('Berkas Kartu Keluarga.jpg diunggah.'), 'x-on:click="pratinjau = '));
        $output = $step("Hasil layanan Hasil {$ticket->service->name}.pdf diunggah.");
        $this->assertSame(1, substr_count($output, 'x-on:click="pratinjau = '));
        $this->assertStringContainsString('u0022nama\u0022:\u0022Hasil ' . $ticket->service->name . '.pdf', html_entity_decode($output));
    }

    public function test_a_service_output_that_is_not_a_pdf_only_downloads(): void
    {
        $ticket = $this->completedTicket();
        app(TicketService::class)->uploadOutput($ticket, 'ticket-outputs/hasil.docx', 'Hasil.docx', $this->officer, 'Surat keterangan (Word)');

        $hasil = TicketDocuments::listFor($ticket->fresh(), 'hasil');
        $this->assertSame(['Surat keterangan (Word)', 'DOCX', false, null], [$hasil[0]['nama'], $hasil[0]['jenis'], $hasil[0]['pdf'], $hasil[0]['pratinjau']]);

        $this->actingAs($this->officer)->get(TicketResource::getUrl('view', ['record' => $ticket]))->assertOk()
            ->assertSee('Surat keterangan (Word)')
            ->assertDontSee('data-fi-modal-id="pratinjau-dokumen-hasil"', false);
    }

    public function test_a_ticket_with_only_non_pdf_files_shows_only_download_links(): void
    {
        $applicant = Ticket::oldest('id')->firstOrFail()->user;
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        $ticket = app(TicketService::class)->open(\App\Models\Service::where('name', 'Surat Keterangan Siswa Aktif')->firstOrFail(), $applicant, $counter, 'offline', 'Berkas bukan PDF.');
        foreach (['Rapor.docx', 'Pas Foto.PNG', 'Akta.jpeg', 'catatan.pdf.zip'] as $name) {
            app(TicketService::class)->attachFile($ticket, "ticket-files/{$name}", $name, $counter);
        }

        $berkas = TicketDocuments::listFor($ticket, 'berkas');
        $this->assertSame(['DOCX', 'PNG', 'JPEG', 'ZIP'], array_column($berkas, 'jenis'));
        $this->assertSame([null, null, null, null], array_column($berkas, 'pratinjau'));

        $html = $this->actingAs($this->officer)->get(TicketResource::getUrl('view', ['record' => $ticket]))->assertOk()
            ->assertSeeInOrder(['Rapor.docx', 'Unduh', 'Pas Foto.PNG', 'Unduh', 'Akta.jpeg', 'Unduh', 'catatan.pdf.zip', 'Unduh'])
            ->assertDontSee('data-fi-modal-id="pratinjau-dokumen-', false)
            ->assertDontSee('Buka di tab baru')
            ->getContent();
        $this->assertStringNotContainsString('x-on:click="pratinjau = ', $html);

        $log = $ticket->logs()->where('notes', 'Berkas Rapor.docx diunggah.')->firstOrFail();
        $step = Livewire::test(ListServiceHistory::class)->mountTableAction('view', $log)->html();
        $this->assertStringContainsString('Rapor.docx', $step);
        $this->assertStringNotContainsString('pratinjau-dokumen-langkah', $step);

        // An upper-case extension is still a PDF.
        app(TicketService::class)->attachFile($ticket, 'ticket-files/surat.PDF', 'Surat.PDF', $counter);
        $this->assertNotNull(TicketDocuments::listFor($ticket, 'berkas')[4]['pratinjau']);
    }

    public function test_a_ticket_without_documents_has_no_preview_modal(): void
    {
        $applicant = Ticket::oldest('id')->firstOrFail()->user;
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        $fresh = app(TicketService::class)->open(\App\Models\Service::where('name', 'Surat Keterangan Siswa Aktif')->firstOrFail(), $applicant, $counter, 'offline', 'Tanpa berkas.');

        $this->actingAs($this->officer)->get(TicketResource::getUrl('view', ['record' => $fresh]))->assertOk()
            ->assertSee('Belum ada berkas.')
            ->assertDontSee('data-fi-modal-id="pratinjau-dokumen-berkas"', false);
    }
}
