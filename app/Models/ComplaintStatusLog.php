<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** One stage in a complaint's handling. Audit evidence: only ever added. */
class ComplaintStatusLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['complaint_id', 'from_status', 'to_status', 'actor_id', 'response', 'internal_note'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Riwayat pengaduan tidak dapat diubah.'));
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
