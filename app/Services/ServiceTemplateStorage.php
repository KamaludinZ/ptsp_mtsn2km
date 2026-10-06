<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\ServiceResource\RelationManagers\TemplatesRelationManager;
use App\Models\Service;
use App\Models\ServiceTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Unggah dan penyimpanan berkas template: checks the file by its content
 * (PDF, Word or Excel only, at most MAX_KB), stores it privately per
 * service under a random name, keeps the original name for the download,
 * and removes the stored file again if saving the record fails.
 */
class ServiceTemplateStorage
{
    public const MAX_KB = 5120;

    public const EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    /** @param  array{nama: string, is_required?: bool, sort?: int, petunjuk?: ?string}  $data */
    public function store(Service $service, UploadedFile $file, array $data): ServiceTemplate
    {
        $path = $this->put($service, $file);

        try {
            return DB::transaction(fn () => $service->templates()->create([
                'nama' => trim($data['nama']),
                'file_path' => $path,
                'file_name' => self::cleanName($file->getClientOriginalName()),
                'is_required' => (bool) ($data['is_required'] ?? false),
                'sort' => (int) ($data['sort'] ?? ((int) $service->templates()->max('sort') + 1)),
                'petunjuk' => $data['petunjuk'] ?? null,
            ]));
        } catch (\Throwable $e) {
            Storage::disk(ServiceTemplate::DISK)->delete($path);
            throw $e;
        }
    }

    /** Swap the file of a template (the old file is removed by the model). */
    public function replace(ServiceTemplate $template, UploadedFile $file): ServiceTemplate
    {
        $path = $this->put($template->service, $file);

        try {
            $template->update(['file_path' => $path, 'file_name' => self::cleanName($file->getClientOriginalName())]);
        } catch (\Throwable $e) {
            Storage::disk(ServiceTemplate::DISK)->delete($path);
            throw $e;
        }

        return $template;
    }

    /** Validate by content and store under service-templates/{service}/{random}.{ext}. */
    private function put(Service $service, UploadedFile $file): string
    {
        if (! $file->isValid()) {
            throw new TicketActionException('Berkas gagal diunggah. Silakan coba lagi.');
        }
        if ($file->getSize() > self::MAX_KB * 1024) {
            throw new TicketActionException('Berkas terlalu besar (maksimal ' . (self::MAX_KB / 1024) . ' MB).');
        }

        $mime = $file->getMimeType(); // detected from the content, not the name
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = in_array($mime, TemplatesRelationManager::MIME_TYPES, true)
            // Office files are ZIP containers; some systems report them as such.
            || (in_array($mime, ['application/zip', 'application/octet-stream'], true) && in_array($extension, ['docx', 'xlsx'], true));

        if (! $allowed || ! in_array($extension, self::EXTENSIONS, true)) {
            throw new TicketActionException('Template harus berupa PDF, Word, atau Excel.');
        }

        return $file->storeAs('service-templates/' . $service->id, Str::uuid() . '.' . $extension, ServiceTemplate::DISK);
    }

    /** "Formulir Permohonan (v2).docx" -> safe for the download header. */
    public static function cleanName(string $name): string
    {
        // Not pathinfo(): a "/" in a client file name is not a directory.
        $dot = mb_strrpos($name, '.');
        $extension = $dot === false ? '' : strtolower(mb_substr($name, $dot + 1));
        $base = Str::of($dot === false ? $name : mb_substr($name, 0, $dot))->replaceMatches('/[^\pL\pN ._()-]+/u', ' ')->squish()->limit(120, '');

        return ($base->isEmpty() ? 'template' : (string) $base) . ($extension ? ".{$extension}" : '');
    }
}
