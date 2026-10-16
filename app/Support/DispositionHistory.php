<?php

namespace App\Support;

use App\Enums\DispositionAction;
use App\Enums\SignatureModel;
use App\Models\DispositionLog;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;

/**
 * Riwayat disposisi & tanda tangan for one ticket, read from disposition_logs
 * (actor, role, action, signature model, signed sheet, recipients,
 * instruction, note).
 */
class DispositionHistory
{
    /**
     * @return array<int, array{id: int, at: \Illuminate\Support\Carbon, actor: string, role: ?string, action: string, signature_model: ?string, signature_file: ?string, recipients: array<int, string>, instruction: ?string, note: ?string, acknowledged_by: ?string}>
     */
    public static function forTicket(Ticket $ticket): array
    {
        // Signed sheets uploaded after the decision point at it from the history.
        $laterSheets = $ticket->logs()
            ->where('action', 'file_uploaded')
            ->whereNotNull('ticket_file_id')
            ->whereNotNull('metadata->disposition_log_id')
            ->with('file')
            ->get()
            ->groupBy(fn (TicketLog $log) => $log->metadata['disposition_log_id']);

        return DispositionLog::query()
            ->where('ticket_id', $ticket->id)
            ->with(['actor', 'signatureFile'])
            ->oldest()->oldest('id')
            ->get()
            ->map(function (DispositionLog $disposition) use ($laterSheets) {
                $sheet = $disposition->signatureFile ?? $laterSheets->get($disposition->id)?->last()?->file;

                return [
                    'id' => $disposition->id,
                    'at' => $disposition->created_at,
                    'actor' => $disposition->actor?->name ?? 'Sistem',
                    'role' => $disposition->role
                        ? (RoleAccess::SYSTEM_ROLES[$disposition->role] ?? $disposition->role)
                        : ($disposition->actor?->getRoleNames()->map(fn (string $role) => RoleAccess::SYSTEM_ROLES[$role] ?? $role)->join(', ') ?: null),
                    'action' => $disposition->action->value,
                    'signature_model' => $disposition->signature_model?->value,
                    'signature_file' => $sheet ? route('documents.ticket-file', $sheet) : null,
                    'recipients' => $disposition->recipients ?? [],
                    'instruction' => $disposition->instruction,
                    'note' => $disposition->note,
                    'acknowledged_by' => $disposition->acknowledged_by_name,
                ];
            })
            ->all();
    }

    /** Whether the leader has disposed the ticket (a decision that produces a disposition sheet). */
    public static function disposed(Ticket $ticket): bool
    {
        return DispositionLog::where('ticket_id', $ticket->id)
            ->whereIn('action', [DispositionAction::Disposisi, DispositionAction::Acknowledge])
            ->exists();
    }

    /**
     * Who may open the printable disposition sheet: leaders who may dispose the
     * ticket, and once it is disposed, staff who may see the ticket (e.g. the
     * receiving units) as proof.
     */
    public static function canPrintSheet(Ticket $ticket, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('approve', $ticket)
            || ($user->isStaff() && $user->can('view', $ticket) && self::disposed($ticket));
    }

    public static function action(?string $action): string
    {
        return DispositionAction::tryFrom((string) $action)?->label() ?? '–';
    }

    public static function actionColor(?string $action): string
    {
        return DispositionAction::tryFrom((string) $action)?->color() ?? 'gray';
    }

    public static function signatureModel(?string $model): string
    {
        return SignatureModel::tryFrom((string) $model)?->label() ?? '–';
    }
}
