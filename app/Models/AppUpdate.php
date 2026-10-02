<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One application update: where it came from, the versions, who and when. */
class AppUpdate extends Model
{
    public const SOURCES = ['github' => 'Rilis GitHub', 'upload' => 'Unggah berkas'];

    public const STATUSES = ['pending' => 'Menunggu', 'applied' => 'Diterapkan', 'failed' => 'Gagal'];

    protected $fillable = ['source', 'from_version', 'to_version', 'status', 'package_path', 'package_name', 'notes', 'performed_by', 'finished_at'];

    protected $casts = ['finished_at' => 'datetime'];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
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
