<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    public static function exists(?string $path): bool
    {
        return $path !== null && $path !== ''
            && (Storage::disk('local')->exists($path) || Storage::disk('public')->exists($path));
    }

    public static function download(?string $path, ?string $name = null): StreamedResponse
    {
        abort_unless(self::exists($path), 404, 'File tidak ditemukan');

        $disk = Storage::disk('local')->exists($path) ? 'local' : 'public';

        return Storage::disk($disk)->download($path, $name ?: basename($path));
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
            Storage::disk('public')->delete($path);
        }
    }
}
