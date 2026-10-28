<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\TicketFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Applicant documents (requirements such as KTP/rapor, and service outputs)
 * are personal data: they are stored on the private "local" disk and only
 * served through controllers that authorize the request. Files uploaded
 * before this change still live on the public disk and are read from there.
 */
class TicketDocuments
{
    /** Extensions accepted for applicant documents and service outputs. */
    public const MIMES = 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip';

    /** The same formats as MIME types, for upload fields. */
    public const MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
    ];

    /** Requirement documents from applicants: PDF, images and Word files. */
    public const REQUIREMENT_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** Is the document a PDF (by its file name or stored path)? */
    public static function isPdf(?string $name): bool
    {
        return strtolower(pathinfo((string) $name, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * The documents of one ticket as its detail page lists them: the
     * requirement files ("berkas", oldest first) or the service output
     * ("hasil"); a PDF also has its pratinjau address (previewUrl).
     *
     * @return array<int, array{nama: string, keterangan: string, jenis: string, pdf: bool, unduh: string, pratinjau: ?string}>
     */
    public static function listFor(Ticket $ticket, string $kind): array
    {
        if ($kind === 'hasil') {
            $output = $ticket->output;

            return $output ? [[
                'nama' => $output->output_description ?: 'Hasil layanan',
                'keterangan' => 'Diunggah ' . $output->created_at?->format('d M Y H:i'),
                'jenis' => self::type($output->file_path),
                'pdf' => self::isPdf($output->file_path),
                'unduh' => route('documents.ticket-output', $output),
                'pratinjau' => self::previewUrl($output->file_path, route('documents.ticket-output', $output)),
            ]] : [];
        }

        return $ticket->files()->with('uploader:id,name')->oldest()->oldest('id')->get()
            ->map(fn (TicketFile $file) => [
                'nama' => $file->file_name,
                'keterangan' => 'Diunggah ' . $file->created_at?->format('d M Y H:i') . ($file->uploader ? " · {$file->uploader->name}" : ''),
                'jenis' => self::type($file->file_name),
                'pdf' => self::isPdf($file->file_name),
                'unduh' => route('documents.ticket-file', $file),
                'pratinjau' => self::previewUrl($file->file_name, route('documents.ticket-file', $file)),
            ])
            ->all();
    }

    /**
     * Where a PDF is shown in the pratinjau modal: its authorized download
     * route, asked to display the file inline. Other files only download.
     */
    public static function previewUrl(?string $name, string $downloadUrl): ?string
    {
        return self::isPdf($name) ? $downloadUrl . '?pratinjau=1' : null;
    }

    /**
     * The file type of a document for the API: its extension and MIME type,
     * and for a PDF where it is previewed (beside its download address).
     *
     * @return array{jenis: ?string, tipe: ?string, pratinjau: ?string}
     */
    public static function fileType(?string $name, string $downloadUrl): array
    {
        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION)) ?: null;

        return [
            'jenis' => $extension,
            'tipe' => $extension ? (MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream') : null,
            'pratinjau' => self::previewUrl($name, $downloadUrl),
        ];
    }

    /** File type shown beside a document: its extension, e.g. "PDF". */
    private static function type(?string $name): string
    {
        return strtoupper(pathinfo((string) $name, PATHINFO_EXTENSION)) ?: 'Berkas';
    }

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    public static function exists(?string $path): bool
    {
        return $path !== null && $path !== ''
            && (Storage::disk('local')->exists($path) || Storage::disk('public')->exists($path));
    }

    /**
     * The service output's file name for the applicant: the ticket number, not
     * the storage's random name, e.g. hasil-layanan-PTSP-202610-0008.pdf.
     */
    public static function outputName(Ticket $ticket): string
    {
        $extension = strtolower(pathinfo((string) $ticket->output?->file_path, PATHINFO_EXTENSION));

        return 'hasil-layanan-' . $ticket->ticket_number . ($extension !== '' ? '.' . $extension : '');
    }

    /** Personal documents: kept by no shared cache (a proxy or a CDN in front). */
    public static function download(?string $path, ?string $name = null): StreamedResponse
    {
        abort_unless(self::exists($path), 404, 'File tidak ditemukan');

        $disk = Storage::disk('local')->exists($path) ? 'local' : 'public';

        return Storage::disk($disk)->download($path, $name ?: basename($path), ['Cache-Control' => 'private, no-store']);
    }

    /**
     * The document for the pratinjau modal: a PDF shown in the page (inline),
     * read from the same private disk. Only a real PDF (by name and by its
     * first bytes) is sent this way; anything else downloads as before.
     */
    public static function preview(?string $path, ?string $name = null): StreamedResponse
    {
        abort_unless(self::exists($path), 404, 'File tidak ditemukan');

        $disk = Storage::disk(Storage::disk('local')->exists($path) ? 'local' : 'public');
        $stream = $disk->readStream($path);
        $isPdf = self::isPdf($path) && $stream && fread($stream, 5) === '%PDF-';
        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $isPdf) {
            return self::download($path, $name);
        }

        return $disk->response($path, $name ?: basename($path), ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store'], 'inline');
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
            Storage::disk('public')->delete($path);
        }
    }
}
