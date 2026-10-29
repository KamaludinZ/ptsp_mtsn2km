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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The ticket APIs describe each document's file type: extension, MIME type and (PDF only) its pratinjau. */
class TicketDocumentApiTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->seed(TicketDocumentSeeder::class);

        // A finished request with a PDF letter, a JPG scan and a PDF service output.
        $this->ticket = Ticket::where('status', 'completed')->whereHas('output')
            ->whereHas('files', fn ($files) => $files->where('file_name', 'Kartu Keluarga.jpg'))->oldest('id')->firstOrFail();
    }

    private function documents(): array
    {
        [$pdf, $jpg] = [$this->ticket->files()->where('file_name', 'Surat Permohonan.pdf')->firstOrFail(), $this->ticket->files()->where('file_name', 'Kartu Keluarga.jpg')->firstOrFail()];

        return [
            'pdf' => ['jenis' => 'pdf', 'tipe' => 'application/pdf', 'pratinjau' => route('documents.ticket-file', $pdf) . '?pratinjau=1', 'unduh' => route('documents.ticket-file', $pdf)],
            'jpg' => ['jenis' => 'jpg', 'tipe' => 'image/jpeg', 'pratinjau' => null, 'unduh' => route('documents.ticket-file', $jpg)],
            'hasil' => ['jenis' => 'pdf', 'tipe' => 'application/pdf', 'pratinjau' => route('documents.ticket-output', $this->ticket->output) . '?pratinjau=1', 'unduh' => route('documents.ticket-output', $this->ticket->output)],
        ];
    }

    private function only(array $document): array
    {
        return ['jenis' => $document['jenis'], 'tipe' => $document['tipe'], 'pratinjau' => $document['pratinjau'], 'unduh' => $document['unduh']];
    }

    public function test_the_staff_ticket_api_gives_each_document_s_type(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $json = $this->getJson('/api/tiket/' . $this->ticket->ticket_number)->assertOk()->json();
        $berkas = collect($json['berkas'])->keyBy('nama');

        $this->assertSame($this->documents()['pdf'], $this->only($berkas['Surat Permohonan.pdf']));
        $this->assertSame($this->documents()['jpg'], $this->only($berkas['Kartu Keluarga.jpg']));
        $this->assertSame($this->documents()['hasil'], $this->only($json['hasil_layanan']));
    }

    public function test_the_applicant_api_gives_the_types_and_keeps_the_old_hasil_address(): void
    {
        Sanctum::actingAs($this->ticket->user);
        $json = $this->getJson('/api/permohonan/' . $this->ticket->ticket_number)->assertOk()->json();
        $berkas = collect($json['berkas'])->keyBy('nama');

        $this->assertSame($this->documents()['pdf'], $this->only($berkas['Surat Permohonan.pdf']));
        $this->assertSame($this->documents()['jpg'], $this->only($berkas['Kartu Keluarga.jpg']));
        // The product through the API itself (a token can use it); "hasil" stays the old address.
        $api = route('api.permohonan.hasil', $this->ticket);
        $this->assertSame(array_merge($this->documents()['hasil'], ['pratinjau' => $api . '?pratinjau=1', 'unduh' => $api]), $this->only($json['hasil_layanan']));
        $this->assertSame($this->documents()['hasil']['unduh'], $json['hasil']);

        $waiting = Ticket::whereDoesntHave('output')->oldest('id')->firstOrFail();
        Sanctum::actingAs($waiting->user);
        $this->getJson('/api/permohonan/' . $waiting->ticket_number)->assertOk()->assertJsonPath('hasil', null)->assertJsonPath('hasil_layanan', null);
    }

    public function test_the_history_api_gives_the_type_of_each_step_s_documents(): void
    {
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        app(TicketService::class)->attachFile($this->ticket, 'ticket-files/rapor.docx', 'Rapor.docx', $officer);
        Sanctum::actingAs($officer);

        $documents = collect($this->getJson('/api/tiket/' . $this->ticket->ticket_number . '/riwayat')->assertOk()->json('riwayat'))
            ->pluck('berkas')->filter()->flatten(1)->keyBy('nama');

        $this->assertSame(['jenis' => 'pdf', 'tipe' => 'application/pdf'], array_intersect_key($documents['Surat Permohonan.pdf'], array_flip(['jenis', 'tipe'])));
        $this->assertSame(['jenis' => 'docx', 'tipe' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'pratinjau' => null],
            array_intersect_key($documents['Rapor.docx'], array_flip(['jenis', 'tipe', 'pratinjau'])));
        $this->assertNotNull($documents['Surat Permohonan.pdf']['pratinjau']);
    }
}
