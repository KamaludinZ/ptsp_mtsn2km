<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\TicketLog;

/**
 * Riwayat disposisi & tanda tangan for one ticket, shaped like the planned
 * disposition_logs table (actor, role, action, signature model, signature
 * file, instruction, note). Until that table exists the entries are read
 * from the leadership decisions recorded in ticket_logs.
 */
class DispositionHistory
{
    public const ACTIONS = [
        'disposisi' => 'Didisposisi',
        'reject' => 'Ditolak',
        'acknowledge' => 'Diketahui',
    ];

    /**
     * @return array<int, array{at: \Illuminate\Support\Carbon, actor: string, role: ?string, action: string, signature_model: ?string, signature_file: ?string, instruction: ?string, note: ?string}>
     */
    public static function forTicket(Ticket $ticket): array
    {
        return $ticket->logs()
            ->whereIn('action', ['approved', 'rejected'])
            ->with('performer')
            ->oldest()
            ->get()
            ->map(fn (TicketLog $log) => [
                'at' => $log->created_at,
                'actor' => $log->performer?->name ?? 'Sistem',
                'role' => $log->performer?->getRoleNames()
                    ->map(fn (string $role) => RoleAccess::SYSTEM_ROLES[$role] ?? $role)->join(', ') ?: null,
                'action' => $log->action === 'approved' ? 'disposisi' : 'reject',
                // Older decisions only kept TTE/TTD on the ticket itself.
                'signature_model' => $log->action === 'approved'
                    ? array_search($ticket->signature_type, ServiceDisposition::SIGNATURE_TYPES, true) ?: null
                    : null,
                'signature_file' => null,
                'instruction' => null,
                'note' => $log->notes,
            ])
            ->all();
    }

    public static function action(?string $action): string
    {
        return self::ACTIONS[$action] ?? '–';
    }

    public static function actionColor(?string $action): string
    {
        return match ($action) {
            'disposisi' => 'success',
            'reject' => 'danger',
            default => 'info',
        };
    }

    public static function signatureModel(?string $model): string
    {
        return ServiceDisposition::SIGNATURE_MODELS[$model] ?? '–';
    }
}
