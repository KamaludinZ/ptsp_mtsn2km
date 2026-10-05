<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\TicketLog;

/**
 * Riwayat perubahan berkas & output of one ticket, newest first. Replaced
 * service outputs are deleted from storage, so only their log remains.
 */
class DocumentHistory
{
    /**
     * @return array<int, array{log: TicketLog, type: string, documents: array<string, string>, replaced: bool}>
     */
    public static function forTicket(Ticket $ticket): array
    {
        $uploads = $ticket->logs()
            ->whereIn('action', ['file_uploaded', 'output_uploaded'])
            ->with(['performer:id,name', 'file'])
            ->latest()->latest('id')
            ->get();
        $latestOutput = $uploads->firstWhere('action', 'output_uploaded');

        return $uploads->map(function (TicketLog $log) use ($latestOutput) {
            $isOutput = $log->action === 'output_uploaded';
            $documents = $log->relatedDocuments();

            return [
                'log' => $log,
                'type' => $isOutput ? 'output' : 'berkas',
                'documents' => $documents,
                'replaced' => $isOutput && ! $documents && $log->isNot($latestOutput),
            ];
        })->all();
    }
}
