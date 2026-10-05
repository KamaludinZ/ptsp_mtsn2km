<?php

namespace App\Models;

use App\Support\TicketLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * One step in a ticket's history (riwayat layanan). Audit evidence: entries
 * are only ever added; the model and a database trigger refuse changes.
 */
class TicketLog extends Model
{
    use HasFactory;

    /** Milestones an applicant may see; other entries (notes, internal uploads) stay with staff. */
    public const APPLICANT_VISIBLE = ['created', 'status_changed', 'assigned', 'approved', 'rejected', 'output_uploaded', 'picked_up', 'workflow_completed'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Riwayat layanan tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Riwayat layanan tidak dapat dihapus.'));
    }

    protected $fillable = [
        'ticket_id',
        'action',
        'performed_by',
        'from_status',
        'to_status',
        'notes',
        'ticket_file_id',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function file()
    {
        return $this->belongsTo(TicketFile::class, 'ticket_file_id');
    }

    // Relationship with ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship with user who performed the action
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /** "Diajukan → Diverifikasi", or just the new status; null when unchanged. */
    public function statusChange(): ?string
    {
        if (! $this->to_status) {
            return null;
        }

        return $this->from_status && $this->from_status !== $this->to_status
            ? TicketLabels::status($this->from_status) . ' → ' . TicketLabels::status($this->to_status)
            : TicketLabels::status($this->to_status);
    }

    /**
     * Documents attached in this step as [name => download url]. ticket_logs
     * keeps no file reference, so uploads on the same ticket within a minute
     * of the log entry are matched.
     *
     * @return array<string, string>
     */
    public function relatedDocuments(): array
    {
        if ($this->ticket_file_id && $this->file) {
            return [$this->file->file_name => route('documents.ticket-file', $this->file)];
        }

        if (! $this->created_at || ! in_array($this->action, ['file_uploaded', 'output_uploaded'], true)) {
            return [];
        }

        // Entries written before ticket_file_id existed: match by time.
        $window = [$this->created_at->copy()->subMinute(), $this->created_at->copy()->addMinute()];

        if ($this->action === 'output_uploaded') {
            return TicketOutput::where('ticket_id', $this->ticket_id)->whereBetween('created_at', $window)->get()
                ->mapWithKeys(fn (TicketOutput $output) => [basename((string) $output->file_path) => route('documents.ticket-output', $output)])
                ->all();
        }

        return TicketFile::where('ticket_id', $this->ticket_id)
            ->where('uploaded_by', $this->performed_by)
            ->whereBetween('created_at', $window)
            ->get()
            ->mapWithKeys(fn (TicketFile $file) => [$file->file_name => route('documents.ticket-file', $file)])
            ->all();
    }
}
