<?php

namespace App\Support;

use App\Models\AppUpdate;
use App\Models\Complaint;

/**
 * Label status berwarna: one place that decides the label, color and icon
 * of a status, for the <x-status-badge> component, public pages and APIs.
 * Colors use Filament's names (gray, info, warning, success, danger).
 */
class StatusBadge
{
    public const TYPES = ['ticket', 'complaint', 'approval', 'update'];

    public const ICONS = [
        'gray' => 'clock',
        'info' => 'check',
        'warning' => 'cog',
        'success' => 'check-circle',
        'danger' => 'x-circle',
    ];

    /** @return array{label: string, color: string, icon: string} */
    public static function for(?string $status, string $type = 'ticket'): array
    {
        [$label, $color] = match ($type) {
            'complaint' => [
                Complaint::STATUSES[$status] ?? ucfirst((string) $status),
                match ($status) {
                    'submitted' => 'warning',
                    'in_review', 'in_progress' => 'info',
                    'resolved', 'closed' => 'success',
                    default => 'gray',
                },
            ],
            'approval' => [
                ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$status] ?? ucfirst((string) $status),
                ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$status] ?? 'gray',
            ],
            'update' => [
                AppUpdate::STATUSES[$status] ?? ucfirst((string) $status),
                ['pending' => 'warning', 'applied' => 'success', 'failed' => 'danger'][$status] ?? 'gray',
            ],
            default => [TicketLabels::status($status), TicketLabels::statusColor($status)],
        };

        return ['label' => $label ?: '–', 'color' => $color, 'icon' => self::ICONS[$color] ?? 'clock'];
    }
}
