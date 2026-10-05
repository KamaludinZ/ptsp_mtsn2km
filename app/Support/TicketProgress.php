<?php

namespace App\Support;

use App\Models\Ticket;
use Illuminate\Support\Carbon;

/**
 * Tahapan permohonan for the detail page: Diajukan, Diverifikasi,
 * (Disposisi pimpinan,) Diproses, Selesai, each done / current / upcoming /
 * stopped, with the time it was reached (from the service history), plus
 * the time left until the target date.
 */
class TicketProgress
{
    private const RANK = ['submitted' => 0, 'verified' => 1, 'approved' => 2, 'in_process' => 3, 'completed' => 4];

    /**
     * @return array<int, array{key: string, label: string, state: 'done'|'current'|'upcoming'|'stopped', at: ?Carbon}>
     */
    public static function steps(Ticket $ticket): array
    {
        $reached = $ticket->logs()->whereNotNull('to_status')->oldest()->oldest('id')->get(['to_status', 'created_at'])
            ->groupBy('to_status')->map(fn ($logs) => $logs->first()->created_at);
        $stopped = in_array($ticket->status, ['rejected', 'cancelled'], true);
        $rank = self::RANK[$ticket->status] ?? self::lastRank($reached->keys()->all());
        if ($ticket->approval_status === 'rejected') {
            $rank = max($rank, 1); // leaders only decide on verified requests
        }

        $steps = [['submitted', 'Diajukan', 0, $ticket->created_at], ['verified', 'Diverifikasi', 1, $reached->get('verified')]];
        if ($ticket->approval_required) {
            $steps[] = ['approved', 'Disposisi pimpinan', 2, $ticket->approved_at];
        }
        $steps[] = ['in_process', 'Diproses', 3, $reached->get('in_process')];
        $steps[] = ['completed', 'Selesai', 4, $reached->get('completed') ?? $ticket->actual_completion_date];

        $current = collect($steps)->first(fn ($step) => ! self::isDone($ticket, $step[0], $step[2], $rank));

        $result = collect($steps)->map(function ($step) use ($ticket, $rank, $current, $stopped) {
            [$key, $label, $order, $at] = $step;
            $done = self::isDone($ticket, $key, $order, $rank);

            return [
                'key' => $key,
                'label' => $label,
                'state' => match (true) {
                    $done => 'done',
                    $current && $current[0] === $key => $stopped ? 'stopped' : 'current',
                    default => 'upcoming',
                },
                'at' => $done || ($current && $current[0] === $key && $stopped) ? ($at ? Carbon::parse($at) : null) : null,
            ];
        })->all();

        if ($stopped) {
            foreach ($result as $i => $step) {
                if ($step['state'] === 'stopped') {
                    $result[$i]['label'] = $ticket->status === 'rejected' ? 'Ditolak' : 'Dibatalkan';
                    $result[$i]['at'] = $reached->get($ticket->status) ?? $ticket->updated_at;
                }
            }
        }

        return $result;
    }

    /**
     * Riwayat status: every status change, oldest first, with who changed it
     * and how long the request stayed in that status (until the next change,
     * or until now for the current one).
     *
     * @return array<int, array{from: ?string, to: string, at: Carbon, actor: string, notes: ?string, duration: string, current: bool}>
     */
    public static function statusHistory(Ticket $ticket): array
    {
        $changes = $ticket->logs()->with('performer:id,name')->whereNotNull('to_status')->oldest()->oldest('id')->get()
            ->filter(fn ($log, $i) => $i === 0 || $log->from_status !== $log->to_status)
            ->values();
        $open = in_array($ticket->status, Ticket::OPEN_STATUSES, true);

        return $changes->map(function ($log, int $i) use ($changes, $open) {
            $next = $changes->get($i + 1);
            $current = $next === null;
            $until = $next?->created_at ?? ($open ? now() : null);

            return [
                'from' => $log->from_status,
                'to' => $log->to_status,
                'at' => $log->created_at,
                'actor' => $log->performer?->name ?? 'Sistem',
                'notes' => $log->notes,
                'duration' => $until ? self::duration($log->created_at, $until) : 'Status akhir',
                'current' => $current,
            ];
        })->all();
    }

    /** "2 hari 3 jam", "45 menit", "< 1 menit". */
    public static function duration(Carbon $from, Carbon $until): string
    {
        $minutes = (int) $from->diffInMinutes($until, true);

        return match (true) {
            $minutes < 1 => '< 1 menit',
            $minutes < 60 => "{$minutes} menit",
            $minutes < 1440 => intdiv($minutes, 60) . ' jam' . ($minutes % 60 ? ' ' . ($minutes % 60) . ' menit' : ''),
            default => intdiv($minutes, 1440) . ' hari' . (intdiv($minutes % 1440, 60) ? ' ' . intdiv($minutes % 1440, 60) . ' jam' : ''),
        };
    }

    /** "Sisa 3 hari", "Jatuh tempo hari ini", "Terlambat 2 hari"; null when closed or without target. */
    public static function deadline(Ticket $ticket): ?array
    {
        if (! $ticket->estimated_completion_date || ! in_array($ticket->status, Ticket::OPEN_STATUSES, true)) {
            return null;
        }

        $days = (int) today()->diffInDays($ticket->estimated_completion_date->copy()->startOfDay(), false);

        return match (true) {
            $days < 0 => ['label' => 'Terlambat ' . abs($days) . ' hari', 'color' => 'danger'],
            $days === 0 => ['label' => 'Jatuh tempo hari ini', 'color' => 'warning'],
            $days <= 2 => ['label' => "Sisa {$days} hari", 'color' => 'warning'],
            default => ['label' => "Sisa {$days} hari", 'color' => 'success'],
        };
    }

    private static function isDone(Ticket $ticket, string $key, int $order, int $rank): bool
    {
        if ($key === 'approved') {
            return $ticket->approval_status === 'approved';
        }

        return $key === 'submitted' || $rank >= $order;
    }

    /** For rejected/cancelled tickets: how far they got before stopping. */
    private static function lastRank(array $statuses): int
    {
        return collect($statuses)->map(fn ($status) => self::RANK[$status] ?? -1)->max() ?? 0;
    }
}
