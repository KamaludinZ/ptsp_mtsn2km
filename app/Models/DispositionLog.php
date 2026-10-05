<?php

namespace App\Models;

use App\Enums\DispositionAction;
use App\Enums\SignatureModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One leadership decision on a ticket (riwayat disposisi). Audit evidence:
 * only ever added, never changed (also enforced by a database trigger).
 */
class DispositionLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['ticket_id', 'ticket_log_id', 'actor_id', 'role', 'action', 'signature_model', 'signature_file_id', 'recipients', 'instruction', 'note', 'acknowledged_by_name'];

    protected $casts = [
        'action' => DispositionAction::class,
        'signature_model' => SignatureModel::class,
        'recipients' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Riwayat disposisi tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Riwayat disposisi tidak dapat dihapus.'));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function signatureFile(): BelongsTo
    {
        return $this->belongsTo(TicketFile::class, 'signature_file_id');
    }

    public function auditEntry(): BelongsTo
    {
        return $this->belongsTo(TicketLog::class, 'ticket_log_id');
    }
}
