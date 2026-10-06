<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** A blank form the applicant downloads, fills in and uploads with the request. */
class ServiceTemplate extends Model
{
    public const DISK = 'local';

    protected $fillable = ['service_id', 'nama', 'file_path', 'file_name', 'mime_type', 'file_size', 'is_required', 'sort', 'versi', 'is_active', 'petunjuk'];

    protected $attributes = ['versi' => 1, 'is_active' => true];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'versi' => 'integer',
        'sort' => 'integer',
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        // Type and size are read once, when the file is set.
        static::saving(function (ServiceTemplate $template) {
            if ($template->isDirty('file_path')) {
                $disk = Storage::disk(self::DISK);
                $exists = filled($template->file_path) && $disk->exists($template->file_path);
                $template->mime_type = $exists ? $disk->mimeType($template->file_path) : null;
                $template->file_size = $exists ? $disk->size($template->file_path) : null;

                // A new file is a new version of the template.
                if ($template->exists) {
                    $template->versi = (int) $template->getOriginal('versi') + 1;
                }
            }
        });

        static::deleted(fn (ServiceTemplate $template) => Storage::disk(self::DISK)->delete($template->file_path));

        // A replaced file is not kept.
        static::updated(function (ServiceTemplate $template) {
            if ($template->wasChanged('file_path') && $template->getOriginal('file_path')) {
                Storage::disk(self::DISK)->delete($template->getOriginal('file_path'));
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Public download link (null when the service is gone). */
    public function downloadUrl(): ?string
    {
        return $this->service ? route('onlineportal.service.template', [$this->service->slug, $this]) : null;
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /** Templates offered to applicants; inactive ones stay for the record only. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /** Is the file there to download? */
    public function isAvailable(): bool
    {
        return filled($this->file_path) && Storage::disk(self::DISK)->exists($this->file_path);
    }

    /** "docx · 24 KB" for the download list (null when the file is missing). */
    public function fileInfo(): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $size = $this->file_size ?? Storage::disk(self::DISK)->size($this->file_path);

        return strtolower(pathinfo($this->file_name ?: $this->file_path, PATHINFO_EXTENSION))
            . ' · ' . max(1, (int) round($size / 1024)) . ' KB';
    }
}
