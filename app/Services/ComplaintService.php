<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\Complaint;
use App\Models\User;

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
    }

    /** @return array<int, string> stored evidence paths */
    public static function evidence(Complaint $complaint): array
    {
        return json_decode($complaint->evidence_files ?? '[]', true) ?: [];
    }
}
