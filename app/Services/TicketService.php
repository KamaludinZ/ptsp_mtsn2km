<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\DispositionLog;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\TicketLog;
use App\Models\TicketOutput;
use App\Models\User;
use App\Support\Persuratan;
use App\Support\RoleAccess;
use App\Support\ServiceDisposition;
use App\Support\TicketDocuments;
use App\Support\IncomingCategory;
use App\Support\TicketLabels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every change an officer makes to a service ticket (Modul 7-9): assigning,
 * moving it through the service stages, notes and documents, the leader's
 * decision and the hand-over at the counter. Each change is written to the
 * ticket log so the applicant and supervisors can follow it.
 */
class TicketService
{
    /** Stages the back office may move a ticket to. Approval belongs to leaders (Modul 8). */
    public const OFFICER_STATUSES = [
        'submitted' => 'Diajukan',
        'verified' => 'Diverifikasi',
        'in_process' => 'Diproses',
        'rejected' => 'Ditolak',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    /** Allowed officer moves from each status; closed tickets only reopen (admin). */
    public const TRANSITIONS = [
        'submitted' => ['verified', 'in_process', 'rejected', 'cancelled'],
        'verified' => ['in_process', 'submitted', 'rejected', 'cancelled'],
        'approved' => ['in_process', 'completed', 'rejected', 'cancelled'],
        'in_process' => ['completed', 'verified', 'rejected', 'cancelled'],
        'completed' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    /** Short explanation shown next to each choice in the status control. */
    public const STATUS_HINTS = [
        'submitted' => 'Kembalikan ke antrean awal (mis. berkas perlu dicek ulang).',
        'verified' => 'Berkas sudah diperiksa dan lengkap.',
        'in_process' => 'Permohonan sedang dikerjakan petugas/unit.',
        'completed' => 'Layanan selesai; pemohon diberi tahu.',
        'rejected' => 'Permohonan tidak dapat dilayani; wajib menulis alasan.',
        'cancelled' => 'Dibatalkan atas permintaan pemohon atau karena duplikat.',
    ];

    /**
     * Statuses $actor may move $ticket to. Admins may reopen a closed ticket
     * (back to "Diproses").
     *
     * @return array<string, string>
     */
    public function nextStatuses(Ticket $ticket, ?User $actor = null): array
    {
        $next = self::TRANSITIONS[$ticket->status] ?? [];
        if (! $next && $actor?->hasRole('admin') && in_array($ticket->status, ['completed', 'rejected', 'cancelled'], true)) {
            $next = ['in_process'];
        }
        if ($ticket->needsApproval()) {
            $next = array_diff($next, ['completed']);
        }

        return array_intersect_key(self::OFFICER_STATUSES, array_flip($next));
    }

    /** Next free number, e.g. PTSP-202609-0007 (soft-deleted tickets keep theirs). */
    public static function nextTicketNumber(): string
    {
        $prefix = \App\Support\Formats::ticketPrefix();
        $period = now()->format('Ym');

        // Atomic per prefix and month; the first number of a period continues after
        // any ticket already numbered that way (soft-deleted ones keep theirs).
        $number = DB::selectOne(<<<'SQL'
            INSERT INTO ticket_sequences (prefix, period, last_number)
            VALUES (?, ?, (
                SELECT COALESCE(MAX(CAST(RIGHT(ticket_number, 4) AS INTEGER)), 0) + 1
                FROM tickets WHERE ticket_number LIKE ?
            ))
            ON CONFLICT (prefix, period) DO UPDATE SET last_number = ticket_sequences.last_number + 1
            RETURNING last_number
        SQL, [$prefix, $period, "{$prefix}-{$period}-%"])->last_number;

        return \App\Support\Formats::ticketNumber((int) $number, now(), $prefix);
    }

    /**
     * Open a new ticket for a service, with the applicant's documents
     * (paths already stored on the private disk).
     *
     * @param  array<string, string>  $files  stored path => original file name
     */
    public function open(Service $service, User $applicant, User $actor, string $mode, string $description, string $priority = 'normal', array $files = []): Ticket
    {
        // Checked again here: the service may have been switched off while the form was open.
        if (! $service->acceptsRequests($applicant, $mode === 'offline' ? 'offline' : 'online')) {
            foreach (array_keys($files) as $path) {
                TicketDocuments::delete($path);
            }

            throw new TicketActionException(! Service::whereKey($service->getKey())->where('is_active', true)->exists()
                ? 'Layanan ini sedang tidak menerima permohonan.'
                : 'Layanan ini tidak dapat diajukan secara online untuk akun Anda.');
        }

        return DB::transaction(function () use ($service, $applicant, $actor, $mode, $description, $priority, $files) {
            $ticket = Ticket::create([
                'ticket_number' => self::nextTicketNumber(),
                'service_id' => $service->id,
                'user_id' => $applicant->id,
                'created_by' => $actor->id,
                'current_handler_id' => $mode === 'offline' ? $actor->id : null,
                'mode' => $mode,
                'status' => 'submitted',
                'priority' => in_array($priority, ['normal', 'high', 'urgent'], true) ? $priority : 'normal',
                'notes' => $description,
                // The service's disposition mode decides whether leadership must dispose it.
                'approval_required' => (bool) $service->approval_required,
            ]);

            foreach ($files as $path => $name) {
                $this->recordFile($ticket, $path, $name, $actor);
            }

            // First workflow step, when the service has a workflow
            $workflow = $service->workflow;
            $firstStep = $workflow?->steps()->orderBy('step_number')->first();
            if ($firstStep) {
                $ticketWorkflow = $ticket->ticketWorkflows()->create([
                    'workflow_id' => $workflow->id,
                    'current_step_id' => $firstStep->id,
                ]);
                $ticketWorkflow->ticketWorkflowSteps()->create([
                    'workflow_step_id' => $firstStep->id,
                    'status' => 'pending',
                    'notes' => 'Permohonan diajukan',
                ]);
            }

            $this->log($ticket, $actor, 'created', null, 'submitted', $mode === 'offline'
                ? 'Permohonan didaftarkan di loket.'
                : 'Permohonan diajukan secara online.', array_filter([
                    'disposition_mode' => $service->disposition_mode,
                    'signature_recommendation' => $service->signature_recommendation,
                ]));

            $this->notify('ticket_created', $ticket);
            DB::afterCommit(fn () => event(new \App\Events\TicketSubmitted($ticket->fresh(), $actor)));

            return $ticket;
        });
    }

    public function assign(Ticket $ticket, User $staff, User $actor, ?string $notes = null): void
    {
        $from = $ticket->status;

        $this->managed(fn () => $ticket->update([
            'assigned_to_id' => $staff->id,
            'current_handler_id' => $staff->id,
            'status' => in_array($ticket->status, ['submitted', 'verified'], true) ? 'in_process' : $ticket->status,
        ]), $actor);

        $this->log($ticket, $actor, 'assigned', $from, $ticket->status, trim("Tiket ditugaskan kepada {$staff->name}. " . ($notes ?? '')));
    }

    /** Move a ticket to another stage (back office). */
    public function changeStatus(Ticket $ticket, string $status, string $notes, User $actor): void
    {
        if (! array_key_exists($status, self::OFFICER_STATUSES)) {
            throw new TicketActionException('Disposisi diberikan oleh pimpinan melalui menu Disposisi Masuk.');
        }

        if ($status === 'completed' && $ticket->needsApproval()) {
            throw new TicketActionException('Tiket ini belum disetujui pimpinan, sehingga belum bisa diselesaikan.');
        }

        if ($status === $ticket->status) {
            throw new TicketActionException('Tiket sudah berstatus ' . TicketLabels::status($status) . '.');
        }

        if (! array_key_exists($status, $this->nextStatuses($ticket, $actor))) {
            throw new TicketActionException('Status ' . TicketLabels::status($ticket->status) . ' tidak dapat diubah menjadi ' . TicketLabels::status($status) . '.');
        }

        if ($status === 'rejected' && mb_strlen(trim($notes)) < 10) {
            throw new TicketActionException('Tuliskan alasan penolakan (minimal 10 karakter).');
        }

        $from = $ticket->status;

        if ($status === 'completed') {
            $this->managed(fn () => $ticket->markCompleted(), $actor);
        } else {
            $this->managed(fn () => $ticket->update(['status' => $status]), $actor);
        }

        $this->log($ticket, $actor, 'status_changed', $from, $status, $notes);

        $this->notify(match ($status) {
            'completed' => 'ticket_completed',
            'rejected' => 'ticket_rejected',
            default => 'ticket_status_changed',
        }, $ticket, null, ['catatan' => $notes]);
    }

    /** May a kategori layanan masuk be chosen for this ticket (open, past the leader's disposition)? */
    public static function canChooseCategory(Ticket $ticket): bool
    {
        return in_array($ticket->status, Ticket::OPEN_STATUSES, true) && ! $ticket->needsApproval();
    }

    /** Set (or with null, reset to the instruction's) kategori layanan masuk; logged with a reason. */
    public function setIncomingCategory(Ticket $ticket, ?string $category, User $actor, string $reason): void
    {
        if ($category !== null && ! array_key_exists($category, \App\Support\IncomingCategory::CATEGORIES)) {
            throw new TicketActionException('Kategori layanan masuk tidak dikenal.');
        }
        if (! self::canChooseCategory($ticket)) {
            throw new TicketActionException('Kategori hanya dapat dipilih untuk permohonan yang masih berjalan dan sudah melewati disposisi pimpinan.');
        }
        if ($category === $ticket->incoming_category) {
            throw new TicketActionException('Kategori tidak berubah.');
        }
        if (mb_strlen(trim($reason)) < 5) {
            throw new TicketActionException('Tuliskan alasan perubahan kategori (minimal 5 karakter).');
        }

        $from = \App\Support\IncomingCategory::of($ticket);
        $this->managed(fn () => $ticket->update(['incoming_category' => $category]), $actor);
        $to = \App\Support\IncomingCategory::of($ticket);

        $this->log($ticket, $actor, 'category_changed', null, null, trim(
            'Kategori layanan masuk: ' . \App\Support\IncomingCategory::label($from) . ' → ' . \App\Support\IncomingCategory::label($to)
            . ($category === null ? ' (mengikuti instruksi pimpinan)' : '') . '. ' . $reason
        ), ['from' => $from, 'to' => $to, 'manual' => $category !== null, 'reason' => trim($reason)]);
    }

    /** Jenis catatan tindak lanjut. */
    public const FOLLOW_UP_TYPES = [
        'internal' => 'Catatan internal',
        'contact_applicant' => 'Menghubungi pemohon',
        'request_documents' => 'Meminta kelengkapan berkas',
        'coordination' => 'Koordinasi dengan unit lain',
        'other' => 'Lainnya',
    ];

    /**
     * Catatan tindak lanjut. Internal by default; with $toApplicant it is
     * logged as a message the applicant sees on the portal and tracking.
     */
    public function addNote(Ticket $ticket, string $note, User $actor, ?string $type = null, bool $toApplicant = false, ?string $nextFollowUp = null): TicketLog
    {
        if ($type !== null && ! array_key_exists($type, self::FOLLOW_UP_TYPES)) {
            throw new TicketActionException('Jenis tindak lanjut tidak dikenal.');
        }
        if ($nextFollowUp !== null && Carbon::parse($nextFollowUp)->isBefore(today())) {
            throw new TicketActionException('Tanggal tindak lanjut berikutnya tidak boleh di masa lalu.');
        }

        return $this->log($ticket, $actor, $toApplicant ? 'applicant_note' : 'note_added', null, null, trim($note), array_filter([
            'follow_up_type' => $type,
            'next_follow_up_at' => $nextFollowUp ? Carbon::parse($nextFollowUp)->toDateString() : null,
        ]));
    }

    /** A supporting document added by an officer. */
    public function attachFile(Ticket $ticket, string $path, string $name, User $actor, ?string $description = null): void
    {
        $file = $this->recordFile($ticket, $path, $name, $actor);

        $this->log($ticket, $actor, 'file_uploaded', null, null, trim("Berkas {$name} diunggah. " . ($description ?? '')), ['file_name' => $name], $file);
    }

    /** The service product (Modul 9). Replaces an earlier output and closes the ticket. */
    public function uploadOutput(Ticket $ticket, string $path, string $name, User $actor, ?string $description = null): void
    {
        if ($ticket->needsApproval()) {
            TicketDocuments::delete($path);

            throw new TicketActionException('Hasil layanan baru bisa diunggah setelah tiket disetujui pimpinan.');
        }

        DB::transaction(function () use ($ticket, $path, $name, $actor, $description) {
            if ($old = $ticket->output) {
                TicketDocuments::delete($old->file_path);
                $old->delete();
            }

            TicketOutput::create([
                'ticket_id' => $ticket->id,
                'output_type' => 'digital', // an uploaded file; physical products are collected at the counter
                'file_path' => $path,
                'output_description' => $description,
            ]);

            $from = $ticket->status;
            if ($ticket->status !== 'completed') {
                $this->managed(fn () => $ticket->markCompleted(), $actor);
            }

            $this->log($ticket, $actor, 'output_uploaded', $from, $ticket->status, "Hasil layanan {$name} diunggah.", ['file_name' => $name, 'replaced' => (bool) $old]);

            if ($from !== 'completed') {
                $this->notify('ticket_completed', $ticket);
            }
        });
    }

    public function completeWorkflowStep(Ticket $ticket, int $workflowStepId, User $actor, ?string $notes = null): void
    {
        $step = $ticket->workflowSteps()
            ->where('workflow_step_id', $workflowStepId)
            ->with('workflowStep')
            ->first();

        if (! $step) {
            throw new TicketActionException('Langkah workflow tidak ditemukan pada tiket ini.');
        }

        if ($step->completed_at) {
            throw new TicketActionException('Langkah workflow ini sudah diselesaikan.');
        }

        $step->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
            'completed_by' => $actor->id,
        ]);

        $this->log($ticket, $actor, 'workflow_step_completed', null, null, trim("Langkah workflow '{$step->workflowStep?->name}' diselesaikan. " . ($notes ?? '')));

        $total = $ticket->service?->workflow?->steps()->count() ?? 0;
        $done = $ticket->workflowSteps()->whereNotNull('completed_at')->count();

        if ($total > 0 && $done >= $total && ! $ticket->needsApproval()) {
            $from = $ticket->status;
            $this->managed(fn () => $ticket->markCompleted(), $actor);
            $ticket->ticketWorkflows()->latest()->first()?->update(['completed_at' => now()]);

            $this->log($ticket, $actor, 'workflow_completed', $from, 'completed', 'Seluruh workflow telah diselesaikan.');
            $this->notify('ticket_completed', $ticket);
        }
    }

    /** A leader approves or rejects a verified ticket (Modul 8). */
    public function decide(Ticket $ticket, bool $approve, User $leader, ?string $signatureType = null, ?string $notes = null, array $metadata = [], ?TicketFile $signedSheet = null): DispositionLog
    {
        if (! $leader->can('approve', $ticket)) {
            throw new TicketActionException('Anda tidak berwenang memutuskan permohonan ini.');
        }

        if (! Ticket::whereKey($ticket->id)->awaitingApproval()->exists()) {
            throw new TicketActionException('Tiket ini tidak sedang menunggu disposisi.');
        }

        if ($approve && ! in_array($signatureType, ServiceDisposition::SIGNATURE_TYPES, true)) {
            throw new TicketActionException('Pilih model tanda tangan disposisi.');
        }

        if (! $approve && blank($notes)) {
            throw new TicketActionException('Tuliskan alasan penolakan.');
        }

        $from = $ticket->status;

        $this->managed(fn () => $ticket->update([
            'approval_status' => $approve ? 'approved' : 'rejected',
            'is_approved' => $approve,
            'approved_by' => $leader->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'signature_type' => $approve ? $signatureType : null,
            'status' => $approve ? 'approved' : 'rejected',
        ]), $leader);

        $entry = $this->log($ticket, $leader, $approve ? 'approved' : 'rejected', $from, $ticket->status, $approve
            ? trim('Didisposisi pimpinan (' . ServiceDisposition::signatureTypeLabel($signatureType) . '). ' . ($notes ?? ''))
            : 'Ditolak pimpinan: ' . $notes, $metadata + array_filter(['signature_type' => $approve ? $signatureType : null]));

        $this->notify($approve ? 'ticket_status_changed' : 'ticket_rejected', $ticket, null, ['catatan' => $approve ? '' : (string) $notes]);

        return DispositionLog::create([
            'ticket_id' => $ticket->id,
            'ticket_log_id' => $entry->id,
            'actor_id' => $leader->id,
            'role' => $leader->getRoleNames()->first(fn (string $role) => in_array($role, RoleAccess::LEADERSHIP, true)),
            'action' => $approve ? 'disposisi' : 'reject',
            'signature_model' => $approve ? ($metadata['signature_model'] ?? (array_search($signatureType, ServiceDisposition::SIGNATURE_TYPES, true) ?: null)) : null,
            'signature_file_id' => $signedSheet?->id,
            'recipients' => $metadata['recipients'] ?? null,
            'instruction' => $metadata['instruction'] ?? null,
            'note' => $approve ? ($metadata['note'] ?? (blank($metadata) ? $notes : null)) : $notes,
            'acknowledged_by_name' => $approve ? ($metadata['acknowledged_by'] ?? null) : null,
        ]);
    }

    /**
     * Panel aksi disposisi: dispose a ticket to back-office units with an
     * instruction and a signature model, optionally attaching the signed sheet.
     *
     * @param  array<int, string>  $recipients  ServiceDisposition::RECIPIENTS keys
     * @param  ?string  $acknowledgedBy  for "telah didisposisi oleh": the leader it was disposed on behalf of
     */
    public function dispose(Ticket $ticket, User $leader, string $signatureModel, array $recipients = [], ?string $instruction = null, ?string $notes = null, ?string $signatureFile = null, ?string $signatureFileName = null, ?string $acknowledgedBy = null): void
    {
        $signatureType = ServiceDisposition::SIGNATURE_TYPES[$signatureModel] ?? null;
        $acknowledgedBy = $signatureModel === 'acknowledged_by' && filled($acknowledgedBy) ? Str::squish($acknowledgedBy) : null;

        $summary = collect([
            $acknowledgedBy ? "Telah didisposisi oleh {$acknowledgedBy}." : null,
            $recipients ? 'Diteruskan kepada: ' . ServiceDisposition::recipients($recipients) . '.' : null,
            filled($instruction) ? 'Instruksi: ' . trim($instruction) . '.' : null,
            filled($notes) ? trim($notes) : null,
        ])->filter()->join(' ');

        $metadata = array_filter([
            'signature_model' => $signatureModel,
            'recipients' => $recipients ?: null,
            'instruction' => filled($instruction) ? trim($instruction) : null,
            'note' => filled($notes) ? trim($notes) : null,
            'acknowledged_by' => $acknowledgedBy,
        ]);

        // Forwarded to the units picked now, or to the service's usual recipients.
        $units = array_values(array_intersect($recipients ?: (array) $ticket->service?->disposition_roles, array_keys(ServiceDisposition::RECIPIENTS)));

        DB::transaction(function () use ($ticket, $leader, $signatureType, $summary, $signatureFile, $signatureFileName, $metadata, $units) {
            // Recorded first so the disposition row can point at it (rows are immutable).
            $sheet = $signatureFile ? $this->recordFile($ticket, $signatureFile, $signatureFileName ?: basename($signatureFile), $leader) : null;

            $disposition = $this->decide($ticket, true, $leader, $signatureType, $summary ?: null, $metadata, $sheet);
            $this->managed(fn () => $ticket->update([
                'disposition_recipients' => $units ?: null,
                'disposition_category' => IncomingCategory::forInstruction($metadata['instruction'] ?? null),
            ]), $leader);

            // The units' staff learn there is work for them.
            if ($units) {
                foreach (User::role($units)->where('is_active', true)->get() as $holder) {
                    $this->notify('disposition_assigned', $ticket, $holder, ['catatan' => $metadata['instruction'] ?? '']);
                }
            }

            if ($sheet) {
                $this->log($ticket, $leader, 'file_uploaded', null, null, "Lembar disposisi bertanda tangan {$sheet->file_name} diunggah.", [
                    'file_name' => $sheet->file_name,
                    'disposition_log_id' => $disposition->id,
                ], $sheet);
            }

            Persuratan::rememberManual(['instruksi_disposisi' => $metadata['instruction'] ?? null]);
        });
    }

    /**
     * The signed disposition sheet (TTD scan or TTE file), uploaded with the
     * decision or later. A disposition row is immutable, so a later upload
     * is linked through the ticket's history instead of the row.
     */
    public function attachSignedSheet(DispositionLog $disposition, string $path, string $name, User $actor): TicketFile
    {
        $file = $this->recordFile($disposition->ticket, $path, $name, $actor);

        $this->log($disposition->ticket, $actor, 'file_uploaded', null, null, "Lembar disposisi bertanda tangan {$name} diunggah.", [
            'file_name' => $name,
            'disposition_log_id' => $disposition->id,
        ], $file);

        return $file;
    }

    /** Hand a finished product over to the applicant at the counter (Modul 9). */
    public function handOver(Ticket $ticket, User $actor): void
    {
        if ($ticket->status !== 'completed' || ! $ticket->ready_for_pickup) {
            throw new TicketActionException('Tiket ini tidak sedang menunggu diambil.');
        }

        $ticket->update(['ready_for_pickup' => false]);

        $this->log($ticket, $actor, 'picked_up', null, null, 'Produk layanan diserahkan kepada pemohon di loket.');
    }

    private function recordFile(Ticket $ticket, string $path, string $name, User $actor): TicketFile
    {
        return TicketFile::create([
            'ticket_id' => $ticket->id,
            'file_name' => $name,
            'file_path' => $path,
            'file_type' => pathinfo($name, PATHINFO_EXTENSION) ?: null,
            'uploaded_by' => $actor->id,
        ]);
    }

    /**
     * Tell the applicant (or another recipient) by the notification's
     * templates, once the change is committed: a rolled-back change sends
     * nothing.
     */
    private function notify(string $event, Ticket $ticket, ?User $recipient = null, array $extra = []): void
    {
        DB::afterCommit(fn () => app(NotificationDispatcher::class)->send($event, $ticket->fresh(['service', 'user']) ?? $ticket, $recipient, $extra));
    }

    /**
     * Run a ticket change whose history this service records itself. $actor
     * is who the riwayat status (database trigger) records as the changer.
     */
    private function managed(callable $change, ?User $actor = null): mixed
    {
        [$previous, $previousActor] = [Ticket::$historyManaged, Ticket::$actingUserId];
        Ticket::$historyManaged = true;
        Ticket::$actingUserId = $actor?->id ?? $previousActor;

        try {
            return $change();
        } finally {
            [Ticket::$historyManaged, Ticket::$actingUserId] = [$previous, $previousActor];
        }
    }

    /** One immutable step in the ticket's history (riwayat layanan). */
    private function log(Ticket $ticket, User $actor, string $action, ?string $from, ?string $to, ?string $notes, array $metadata = [], ?TicketFile $file = null): TicketLog
    {
        return TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => $action,
            'performed_by' => $actor->id,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'ticket_file_id' => $file?->id,
            'metadata' => $metadata ?: null,
            // Artisan commands have no client; their fake request would report 127.0.0.1.
            'ip_address' => app()->runningInConsole() && ! app()->runningUnitTests() ? null : request()->ip(),
        ]);
    }
}
