<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One application update: where it came from, the versions, who and when. */
class AppUpdate extends Model
{
    public const SOURCES = ['github' => 'Rilis GitHub', 'upload' => 'Unggah berkas', 'deploy' => 'Redeploy (GitHub/Coolify)'];

    public const STATUSES = ['pending' => 'Menunggu', 'applied' => 'Diterapkan', 'failed' => 'Gagal'];

    protected $fillable = ['source', 'from_version', 'to_version', 'status', 'package_path', 'package_name', 'notes', 'performed_by', 'finished_at'];

    protected $casts = ['finished_at' => 'datetime'];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** API shape of one riwayat pembaruan entry. */
    public function toHistory(): array
    {
        return [
            'id' => $this->id,
            'waktu' => $this->created_at?->toIso8601String(),
            'selesai' => $this->finished_at?->toIso8601String(),
            'sumber' => $this->source,
            'sumber_label' => $this->sourceLabel(),
            'dari' => $this->from_version,
            'ke' => $this->to_version,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'paket' => $this->package_name,
            'oleh' => $this->performer?->name,
            'catatan' => $this->notes,
        ];
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'applied' => 'success',
            'failed' => 'danger',
            default => 'warning',
        };
    }
}
