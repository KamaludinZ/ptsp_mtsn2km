<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pengumuman extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'publish_date',
        'end_date',
        'is_active',
        'author',
        'user_id',
        'attachment',
        'url',
        'view_count',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        // The attachment goes with the announcement.
        static::deleted(function (Pengumuman $item) {
            if ($item->attachment) {
                \Illuminate\Support\Facades\Storage::disk(config('filament.default_filesystem_disk', 'public'))->delete($item->attachment);
            }
        });
    }

    /** On the public site now: active, published, and the end date (inclusive) not passed. */
    public function scopeActive($query)
    {
        return \App\Support\ContentStatus::scope($query, 'tayang');
    }

    /** Unique viewers, one row per IP address (pengumuman_views). */
    public function views(): HasMany
    {
        return $this->hasMany(PengumumanView::class);
    }

    public function getUniqueViewCountAttribute(): int
    {
        return $this->views()->count();
    }

    /** One of ContentStatus::LABELS: draf, dijadwalkan, tayang or berakhir. */
    public function status(): string
    {
        return \App\Support\ContentStatus::of($this);
    }

    public function scopeWithStatus($query, string $status)
    {
        return \App\Support\ContentStatus::scope($query, $status);
    }

    /** Shown as the writer: the author field, else the account that wrote it. */
    public function authorName(): string
    {
        return filled($this->author) ? $this->author : ($this->user?->name ?? 'Admin');
    }

    /**
     * Count a visit once per IP address. Safe for simultaneous visits: the
     * unique key decides, and only the visit that inserted bumps view_count.
     */
    public function recordView(?string $ip): bool
    {
        if (blank($ip)) {
            return false;
        }

        return DB::transaction(function () use ($ip) {
            $now = now();
            $inserted = PengumumanView::query()->insertOrIgnore([
                'pengumuman_id' => $this->id,
                'ip_address' => $ip,
                'created_at' => $now,
                'updated_at' => $now,
            ]) > 0;

            if ($inserted) {
                $this->increment('view_count');
            }

            return $inserted;
        });
    }

    /** Categories already in use, for suggestions and filters. */
    public static function categories(bool $onlyPublished = false): array
    {
        return static::query()
            ->when($onlyPublished, fn ($q) => $q->active())
            ->whereNotNull('category')->where('category', '<>', '')
            ->distinct()->orderBy('category')->pluck('category')->all();
    }
}
