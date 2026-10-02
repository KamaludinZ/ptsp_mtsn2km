<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** A blank form the applicant downloads, fills in and uploads with the request. */
class ServiceTemplate extends Model
{
    public const DISK = 'local';

    protected $fillable = ['service_id', 'nama', 'file_path', 'file_name', 'is_required', 'sort'];

    protected $casts = [
        'is_required' => 'boolean',
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (ServiceTemplate $template) => Storage::disk(self::DISK)->delete($template->file_path));
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function downloadUrl(): string
    {
        return route('onlineportal.service.template', [$this->service->slug, $this]);
    }

    /** "docx · 24 KB" for the download list. */
    public function fileInfo(): ?string
    {
        if (! Storage::disk(self::DISK)->exists($this->file_path)) {
            return null;
        }

        return strtolower(pathinfo($this->file_name ?: $this->file_path, PATHINFO_EXTENSION))
            . ' · ' . max(1, (int) round(Storage::disk(self::DISK)->size($this->file_path) / 1024)) . ' KB';
    }
}
