<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Complaint and whistleblowing follow-up (Modul 10): the stage, handler,
 * priority and the reply to the reporter.
 */
class ComplaintService
{
    /**
     * @param  array{status: string, priority: string, assigned_to?: ?int, response?: ?string, resolution_notes?: ?string}  $data
     */
    public function followUp(Complaint $complaint, array $data, User $actor): void
    {
        if (! array_key_exists($data['status'], Complaint::STATUSES) || ! array_key_exists($data['priority'], Complaint::PRIORITIES)) {
            throw new TicketActionException('Status atau prioritas tidak dikenal.');
        }

        $finished = in_array($data['status'], ['resolved', 'closed'], true);
        $response = filled($data['response'] ?? null) ? $data['response'] : $complaint->response;

        if ($finished && blank($response)) {
            throw new TicketActionException('Isi tanggapan untuk pelapor sebelum menyelesaikan laporan.');
        }

        $from = $complaint->status;
        $replyChanged = filled($data['response'] ?? null) && $data['response'] !== $complaint->response;

        $complaint->update([
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'assigned_to_id' => $data['assigned_to'] ?? null,
            'response' => $response,
            'resolution_notes' => $data['resolution_notes'] ?? $complaint->resolution_notes,
            'resolved_at' => $finished ? ($complaint->resolved_at ?? now()) : null,
            'resolved_by' => $finished ? ($complaint->resolved_by ?? $actor->id) : null,
        ]);

        if ($from !== $complaint->status || $replyChanged) {
            ComplaintStatusLog::create([
                'complaint_id' => $complaint->id,
                'from_status' => $from,
                'to_status' => $complaint->status,
                'actor_id' => $actor->id,
                'response' => $replyChanged ? $response : null,
                'internal_note' => filled($data['resolution_notes'] ?? null) ? $data['resolution_notes'] : null,
            ]);
        }
    }

    /**
     * Rahasiakan identitas: a handler protects a reporter who did not ask for it
     * (e.g. the report turns out sensitive). One way only, so an identity once
     * hidden is never exposed again by a later click; the reason stays in the log.
     */
    public function makeConfidential(Complaint $complaint, User $actor, string $reason): void
    {
        if ($complaint->isSecret()) {
            throw new TicketActionException('Identitas pelapor sudah dirahasiakan.');
        }

        $complaint->update(['is_confidential' => true]);

        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'from_status' => $complaint->status,
            'to_status' => $complaint->status,
            'actor_id' => $actor->id,
            'internal_note' => 'Identitas pelapor dirahasiakan: ' . trim($reason),
        ]);
    }

    /** Validation rules for a report sent from the public pages (complaint|suggestion|whistleblowing). */
    public static function rulesFor(string $type): array
    {
        return match ($type) {
            'suggestion' => [
                'reporter_name' => 'nullable|string|max:255',
                'reporter_email' => 'nullable|email|max:255',
                'suggestion' => 'required|string|max:2000',
            ],
            'whistleblowing' => [
                'violation_category' => 'nullable|string|max:255',
                'complaint_title' => 'required|string|max:255',
                'complaint_description' => 'required|string|max:2000',
                'incident_date' => 'nullable|date|before_or_equal:today',
                'anonymous' => 'nullable|boolean',
                'reporter_name' => 'nullable|string|max:255',
                'reporter_email' => 'nullable|email|max:255',
                'reporter_phone' => 'nullable|string|max:20',
                'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
            ],
            default => [
                'reporter_name' => 'required|string|max:255',
                'reporter_email' => 'required|email|max:255',
                'reporter_phone' => 'nullable|string|max:20',
                'complaint_title' => 'required|string|max:255',
                'complaint_description' => 'required|string|max:2000',
                'incident_date' => 'nullable|date|before_or_equal:today',
                'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
            ],
        };
    }

    /**
     * Record a report sent from the public pages. Evidence is stored on the
     * private disk: whistleblowing evidence must never have a public URL.
     *
     * @param  array<int, UploadedFile>  $evidence
     */
    public function submit(string $type, array $data, array $evidence = []): Complaint
    {
        $complaint = match ($type) {
            'suggestion' => Complaint::create([
                'complaint_type' => 'suggestion',
                'title' => Str::limit(Str::squish($data['suggestion']), 80),
                'description' => $data['suggestion'],
                'reporter_name' => $data['reporter_name'] ?? null,
                'reporter_email' => $data['reporter_email'] ?? null,
                'status' => 'submitted',
                'anonymous' => empty($data['reporter_name']),
            ]),
            'whistleblowing' => $this->whistleblowing($data),
            default => Complaint::create([
                'complaint_type' => 'complaint',
                'reporter_name' => $data['reporter_name'],
                'reporter_email' => $data['reporter_email'],
                'reporter_phone' => $data['reporter_phone'] ?? null,
                'complainant_name' => $data['reporter_name'],
                'complainant_email' => $data['reporter_email'],
                'complainant_contact' => $data['reporter_phone'] ?? null,
                'title' => $data['complaint_title'],
                'subject' => $data['complaint_title'],
                'description' => $data['complaint_description'],
                'incident_date' => $data['incident_date'] ?? null,
                'status' => 'submitted',
                'anonymous' => false,
            ]),
        };

        if ($evidence) {
            $complaint->update(['evidence_files' => json_encode(array_map(fn (UploadedFile $file) => $file->store('complaint-evidence', 'local'), $evidence))]);
        }

        return $complaint;
    }

    private function whistleblowing(array $data): Complaint
    {
        $anonymous = filter_var($data['anonymous'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return Complaint::create([
            'complaint_type' => 'whistleblowing',
            'category' => $data['violation_category'] ?? null,
            'title' => $data['complaint_title'],
            'subject' => $data['complaint_title'],
            'description' => $data['complaint_description'],
            'incident_date' => $data['incident_date'] ?? null,
            'reporter_name' => $anonymous ? 'Anonim' : ($data['reporter_name'] ?? 'Anonim'),
            'reporter_email' => $anonymous ? null : ($data['reporter_email'] ?? null),
            'reporter_phone' => $anonymous ? null : ($data['reporter_phone'] ?? null),
            'status' => 'submitted',
            'anonymous' => $anonymous,
            'is_whistleblowing' => true,
            'is_confidential' => true,
        ]);
    }

    /** @return array<int, string> stored evidence paths */
    public static function evidence(Complaint $complaint): array
    {
        return json_decode($complaint->evidence_files ?? '[]', true) ?: [];
    }
}
