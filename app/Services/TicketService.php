<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\TicketLog;
use App\Models\TicketOutput;
use App\Models\User;
use App\Support\TicketDocuments;
use App\Support\ServiceDisposition;
use Illuminate\Support\Facades\DB;

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

    /** Next free number, e.g. PTSP-202609-0007 (soft-deleted tickets keep theirs). */
    public static function nextTicketNumber(): string
    {
        $prefix = 'PTSP-' . now()->format('Ym') . '-';

        $last = Ticket::withTrashed()
            ->where('ticket_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(RIGHT(ticket_number, 4) AS INTEGER) DESC')
            ->value('ticket_number');

        return $prefix . sprintf('%04d', $last ? ((int) substr($last, -4)) + 1 : 1);
    }

    /**
     * Open a new ticket for a service, with the applicant's documents
     * (paths already stored on the private disk).
     *
     * @param  array<string, string>  $files  stored path => original file name
     */
    public function open(Service $service, User $applicant, User $actor, string $mode, string $description, string $priority = 'normal', array $files = []): Ticket
    {
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
            ]);

            foreach ($files as $path => $name) {
                $this->recordFile($ticket, $path, $name, $actor);
            }

            // First workflow step, when the service has a workflow
            $workflow = $service->workflow;
            $firstStep = $workflow?->steps()->orderBy('order')->first();
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
                : 'Permohonan diajukan secara online.');

            return $ticket;
        });
    }

    public function assign(Ticket $ticket, User $staff, User $actor, ?string $notes = null): void
    {
        $from = $ticket->status;

        $ticket->update([
            'assigned_to_id' => $staff->id,
            'current_handler_id' => $staff->id,
            'status' => in_array($ticket->status, ['submitted', 'verified'], true) ? 'in_process' : $ticket->status,
        ]);

        $this->log($ticket, $actor, 'assigned', $from, $ticket->status, trim("Tiket ditugaskan kepada {$staff->name}. " . ($notes ?? '')));
    }

    /** Move a ticket to another stage (back office). */
    public function changeStatus(Ticket $ticket, string $status, string $notes, User $actor): void
    {
        if (! array_key_exists($status, self::OFFICER_STATUSES)) {
            throw new TicketActionException('Persetujuan diberikan oleh pimpinan melalui menu Persetujuan.');
        }

        if ($status === 'completed' && $ticket->needsApproval()) {
            throw new TicketActionException('Tiket ini belum disetujui pimpinan, sehingga belum bisa diselesaikan.');
        }

        $from = $ticket->status;

        if ($status === 'completed') {
            $ticket->markCompleted();
        } else {
            $ticket->update(['status' => $status]);
        }

        $this->log($ticket, $actor, 'status_changed', $from, $status, $notes);
    }

    public function addNote(Ticket $ticket, string $note, User $actor): void
    {
        $this->log($ticket, $actor, 'note_added', null, null, $note);
    }

    /** A supporting document added by an officer. */
    public function attachFile(Ticket $ticket, string $path, string $name, User $actor, ?string $description = null): void
    {
        $this->recordFile($ticket, $path, $name, $actor);

        $this->log($ticket, $actor, 'file_uploaded', null, null, trim("Berkas {$name} diunggah. " . ($description ?? '')));
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

            if ($ticket->status !== 'completed') {
                $ticket->markCompleted();
            }

            $this->log($ticket, $actor, 'output_uploaded', null, null, "Hasil layanan {$name} diunggah.");
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
            $ticket->markCompleted();
            $ticket->ticketWorkflows()->latest()->first()?->update(['completed_at' => now()]);

            $this->log($ticket, $actor, 'workflow_completed', null, 'completed', 'Seluruh workflow telah diselesaikan.');
        }
    }

    /** A leader approves or rejects a verified ticket (Modul 8). */
    public function decide(Ticket $ticket, bool $approve, User $leader, ?string $signatureType = null, ?string $notes = null): void
    {
        if (! $leader->can('approve', $ticket)) {
            throw new TicketActionException('Anda tidak berwenang memutuskan permohonan ini.');
        }

        if (! Ticket::whereKey($ticket->id)->awaitingApproval()->exists()) {
            throw new TicketActionException('Tiket ini tidak sedang menunggu persetujuan.');
        }

        if ($approve && ! in_array($signatureType, ServiceDisposition::SIGNATURE_TYPES, true)) {
            throw new TicketActionException('Pilih model tanda tangan disposisi.');
        }

        if (! $approve && blank($notes)) {
            throw new TicketActionException('Tuliskan alasan penolakan.');
        }

        $from = $ticket->status;

        $ticket->update([
            'approval_status' => $approve ? 'approved' : 'rejected',
            'is_approved' => $approve,
            'approved_by' => $leader->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'signature_type' => $approve ? $signatureType : null,
            'status' => $approve ? 'approved' : 'rejected',
        ]);

        $this->log($ticket, $leader, $approve ? 'approved' : 'rejected', $from, $ticket->status, $approve
            ? trim('Didisposisi pimpinan (' . ServiceDisposition::signatureTypeLabel($signatureType) . '). ' . ($notes ?? ''))
            : 'Ditolak pimpinan: ' . $notes);
    }

    /**
     * Panel aksi disposisi: dispose a ticket to back-office units with an
     * instruction and a signature model, optionally attaching the signed sheet.
     *
     * @param  array<int, string>  $recipients  ServiceDisposition::RECIPIENTS keys
     */
    public function dispose(Ticket $ticket, User $leader, string $signatureModel, array $recipients = [], ?string $instruction = null, ?string $notes = null, ?string $signatureFile = null, ?string $signatureFileName = null): void
    {
        $signatureType = ServiceDisposition::SIGNATURE_TYPES[$signatureModel] ?? null;

        $summary = collect([
            $recipients ? 'Diteruskan kepada: ' . ServiceDisposition::recipients($recipients) . '.' : null,
            filled($instruction) ? 'Instruksi: ' . trim($instruction) . '.' : null,
            filled($notes) ? trim($notes) : null,
        ])->filter()->join(' ');

        DB::transaction(function () use ($ticket, $leader, $signatureType, $summary, $signatureFile, $signatureFileName) {
            $this->decide($ticket, true, $leader, $signatureType, $summary ?: null);

            if ($signatureFile) {
                $this->attachFile($ticket, $signatureFile, $signatureFileName ?: basename($signatureFile), $leader, 'Lembar disposisi bertanda tangan.');
            }
        });
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

    private function recordFile(Ticket $ticket, string $path, string $name, User $actor): void
    {
        TicketFile::create([
            'ticket_id' => $ticket->id,
            'file_name' => $name,
            'file_path' => $path,
            'file_type' => pathinfo($name, PATHINFO_EXTENSION) ?: null,
            'uploaded_by' => $actor->id,
        ]);
    }

    private function log(Ticket $ticket, User $actor, string $action, ?string $from, ?string $to, ?string $notes): void
    {
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => $action,
            'performed_by' => $actor->id,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
        ]);
    }
}
