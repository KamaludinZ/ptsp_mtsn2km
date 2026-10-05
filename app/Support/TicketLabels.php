<?php

namespace App\Support;

/**
 * Indonesian labels and badge colours for tickets, shared by the staff panel
 * and the applicant portal.
 */
class TicketLabels
{
    public const STATUSES = ServiceMetrics::STATUS_LABELS + ['pending_approval' => 'Menunggu Persetujuan'];

    public const PRIORITIES = [
        'low' => 'Rendah',
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    public const MODES = [
        'online' => 'Online',
        'offline' => 'Loket',
        'hybrid' => 'Hybrid',
    ];

    public const LOG_ACTIONS = [
        'created' => 'Permohonan dibuat',
        'assigned' => 'Ditugaskan',
        'status_changed' => 'Status diubah',
        'note_added' => 'Catatan',
        'applicant_note' => 'Pesan untuk pemohon',
        'category_changed' => 'Kategori layanan masuk diubah',
        'file_uploaded' => 'Berkas diunggah',
        'output_uploaded' => 'Hasil layanan diunggah',
        'workflow_step_completed' => 'Langkah workflow selesai',
        'workflow_completed' => 'Workflow selesai',
        'approved' => 'Didisposisi pimpinan',
        'rejected' => 'Ditolak pimpinan',
        'picked_up' => 'Diserahkan ke pemohon',
    ];

    public static function status(?string $status): string
    {
        return self::STATUSES[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'submitted' => 'gray',
            'verified' => 'info',
            'in_process', 'pending_approval' => 'warning',
            'approved', 'completed' => 'success',
            'rejected', 'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public static function priority(?string $priority): string
    {
        return self::PRIORITIES[$priority] ?? ucfirst((string) $priority);
    }

    public static function priorityColor(?string $priority): string
    {
        return match ($priority) {
            'high' => 'warning',
            'urgent' => 'danger',
            'low' => 'gray',
            default => 'info',
        };
    }

    public static function mode(?string $mode): string
    {
        return self::MODES[$mode] ?? ucfirst((string) $mode);
    }

    public static function logAction(?string $action): string
    {
        return self::LOG_ACTIONS[$action] ?? ucfirst(str_replace('_', ' ', (string) $action));
    }
}
