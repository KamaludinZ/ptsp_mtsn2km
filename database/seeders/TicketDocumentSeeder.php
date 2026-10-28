<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationDispatcher;
use App\Services\TicketService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Contoh berkas tiket (demo data, never seeded in production): every demo
 * request gets a PDF letter, every other one a scanned image as well, and
 * finished requests a PDF service output, so the document list of the
 * ticket detail page shows both PDF and non-PDF files.
 */
class TicketDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = app(TicketService::class);
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->first();
        if (! $officer) {
            return; // UserSeeder has not run
        }
        $muted = NotificationDispatcher::$muted;
        NotificationDispatcher::$muted = true; // demo applicants must not receive e-mail/WhatsApp

        try {
            Ticket::with(['service:id,name', 'user:id,name', 'creator', 'assignedTo'])->whereDoesntHave('files')->oldest('id')->get()
                ->each(function (Ticket $ticket, int $i) use ($tickets, $officer) {
                    $sender = $ticket->creator ?? $officer;
                    $tickets->attachFile($ticket, $this->pdf('ticket-files', 'Surat Permohonan', $ticket), 'Surat Permohonan.pdf', $sender);
                    if ($i % 2 === 0) {
                        $tickets->attachFile($ticket, $this->image('ticket-files', $ticket), 'Kartu Keluarga.jpg', $sender);
                    }

                    if ($ticket->status === 'completed' && ! $ticket->output) {
                        $tickets->uploadOutput($ticket->fresh(), $this->pdf('ticket-outputs', 'Hasil Layanan', $ticket), "Hasil {$ticket->service?->name}.pdf",
                            $ticket->assignedTo ?? $officer, "{$ticket->service?->name} — {$ticket->ticket_number}");
                    }
                });
        } finally {
            NotificationDispatcher::$muted = $muted;
        }
    }

    /** A one-page PDF on the private disk; returns its stored path. */
    private function pdf(string $directory, string $title, Ticket $ticket): string
    {
        $html = '<h2 style="font-family: sans-serif;">' . e($title) . '</h2>'
            . '<p style="font-family: sans-serif;">Contoh dokumen untuk permohonan ' . e($ticket->ticket_number) . ' — ' . e($ticket->service?->name) . '.</p>'
            . '<p style="font-family: sans-serif;">Pemohon: ' . e($ticket->user?->name) . '</p>'
            . '<p style="font-family: sans-serif; color: #888;">Data demo, bukan dokumen asli.</p>';

        return $this->put($directory, 'pdf', Pdf::loadHTML($html)->setPaper('a4')->output());
    }

    /** A small JPEG "scan" on the private disk; returns its stored path. */
    private function image(string $directory, Ticket $ticket): string
    {
        $image = imagecreatetruecolor(640, 400);
        imagefill($image, 0, 0, imagecolorallocate($image, 241, 245, 249));
        $ink = imagecolorallocate($image, 51, 65, 85);
        imagestring($image, 5, 24, 24, 'KARTU KELUARGA (contoh)', $ink);
        imagestring($image, 4, 24, 60, "Permohonan {$ticket->ticket_number}", $ink);
        imagestring($image, 3, 24, 360, 'Data demo, bukan dokumen asli.', $ink);

        ob_start();
        imagejpeg($image, null, 80);
        $content = (string) ob_get_clean();
        imagedestroy($image);

        return $this->put($directory, 'jpg', $content);
    }

    private function put(string $directory, string $extension, string $content): string
    {
        $path = "{$directory}/" . Str::random(40) . ".{$extension}";
        Storage::disk('local')->put($path, $content);

        return $path;
    }
}
