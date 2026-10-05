<?php

namespace App\Support;

use App\Models\Complaint;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * Service figures shared by every dashboard (front desk, back office,
 * leadership, supervision, admin and applicant portal), so each role sees
 * the same numbers computed the same way.
 */
class ServiceMetrics
{
    public const STATUS_LABELS = [
        'submitted' => 'Diajukan',
        'verified' => 'Diverifikasi',
        'in_process' => 'Diproses',
        'approved' => 'Disetujui',
        'completed' => 'Selesai',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public const PERIODS = [
        'week' => 'Minggu ini',
        'month' => 'Bulan ini',
        'quarter' => 'Triwulan ini',
        'year' => 'Tahun ini',
        'all' => 'Semua data',
    ];

    /** Normalise a ?periode= value, defaulting to the current year. */
    public static function period(?string $period): string
    {
        return array_key_exists((string) $period, self::PERIODS) ? $period : 'year';
    }

    public static function periodStart(string $period): ?CarbonInterface
    {
        return match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => null,
        };
    }

    /**
     * Everything the performance overview (partials.performance-overview)
     * needs for one reporting period.
     */
    public static function overview(string $period): array
    {
        $from = self::periodStart($period);

        return [
            'period' => $period,
            'periods' => self::PERIODS,
            'tickets' => self::tickets($from),
            'services' => self::perService($from),
            'survey' => self::survey($from),
            'complaints' => self::complaints($from),
            'visitors' => self::visitors(),
            'overdueTickets' => Ticket::with('service:id,name')->overdue()
                ->orderBy('estimated_completion_date')->limit(5)->get(),
        ];
    }

    /**
     * Ticket workload and service-standard (SLA) compliance.
     *
     * @param  int|null  $userId  limit to one applicant's tickets
     * @param  (callable(\Illuminate\Database\Eloquent\Builder): mixed)|null  $scope  further limit, e.g. self::scopeFor()
     */
    public static function tickets(?CarbonInterface $from = null, ?CarbonInterface $to = null, ?int $userId = null, ?callable $scope = null): array
    {
        $base = fn () => Ticket::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($scope, fn ($q) => $scope($q));

        $byStatus = $base()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $byMode = $base()->select('mode', DB::raw('count(*) as total'))->groupBy('mode')->pluck('total', 'mode');

        $completed = $base()->where('status', 'completed')->whereNotNull('actual_completion_date');
        $withTarget = (clone $completed)->whereNotNull('estimated_completion_date');
        $withTargetCount = $withTarget->count();
        $onTime = (clone $withTarget)->whereColumn('actual_completion_date', '<=', 'estimated_completion_date')->count();
        $avgDays = (clone $completed)->selectRaw('AVG(actual_completion_date - created_at::date) as days')->value('days');

        $total = (int) $byStatus->sum();
        $done = (int) $byStatus->get('completed', 0);
        $closed = $done + (int) $byStatus->get('rejected', 0) + (int) $byStatus->get('cancelled', 0);

        return [
            'total' => $total,
            'by_status' => collect(self::STATUS_LABELS)->mapWithKeys(fn ($label, $status) => [$status => (int) $byStatus->get($status, 0)])->all(),
            'open' => $total - $closed,
            'completed' => $done,
            'online' => (int) $byMode->get('online', 0),
            'offline' => (int) $byMode->get('offline', 0),
            'overdue' => $base()->overdue()->count(),
            'awaiting_approval' => $base()->awaitingApproval()->count(),
            'ready_for_pickup' => $base()->where('status', 'completed')->where('mode', 'offline')->where('ready_for_pickup', true)->count(),
            'completion_rate' => $total ? (int) round($done / $total * 100) : null,
            'on_time_rate' => $withTargetCount ? (int) round($onTime / $withTargetCount * 100) : null,
            'avg_days' => $avgDays !== null ? round((float) $avgDays, 1) : null,
        ];
    }

    /**
     * The tickets a staff member handles personally ("lingkup saya"): back office
     * their assignments, the counter what it registered, leaders what awaits
     * their decision. Null for roles without a personal workload (whole office).
     */
    public static function scopeFor(User $user): ?callable
    {
        return match (true) {
            $user->hasAnyRole(RoleAccess::LEADERSHIP) && ! $user->hasRole('admin') => fn ($q) => $q->whereIn('id', Ticket::approvableBy($user)->pluck('id')),
            $user->hasRole('back_office') => fn ($q) => $q->where('assigned_to_id', $user->id),
            $user->hasRole('front_desk') => fn ($q) => $q->where('mode', 'offline')->where('created_by', $user->id),
            default => null,
        };
    }

    /** Today's intake and output, shown to every role on the dashboard. */
    public static function today(): array
    {
        $in = Ticket::whereDate('created_at', today());

        return [
            'in' => (clone $in)->count(),
            'in_online' => (clone $in)->where('mode', 'online')->count(),
            'in_offline' => (clone $in)->where('mode', 'offline')->count(),
            'completed' => Ticket::where('status', 'completed')->whereDate('actual_completion_date', today())->count(),
            'rejected' => Ticket::where('status', 'rejected')->whereDate('updated_at', today())->count(),
            'open' => Ticket::open()->count(),
            'overdue' => Ticket::overdue()->count(),
        ];
    }

    public const TREND_RANGES = [
        '7d' => '7 hari terakhir',
        '30d' => '30 hari terakhir',
        '12m' => '12 bulan terakhir',
    ];

    /**
     * Requests received and completed per day (7d, 30d) or per month (12m).
     *
     * @return array<int, array{periode: string, label: string, masuk: int, selesai: int}>
     */
    public static function trend(string $range = '30d'): array
    {
        $monthly = $range === '12m';
        $from = match ($range) {
            '7d' => today()->subDays(6),
            '12m' => today()->startOfMonth()->subMonths(11),
            default => today()->subDays(29),
        };
        $unit = $monthly ? 'month' : 'day';
        $key = $monthly ? 'Y-m' : 'Y-m-d';

        $count = fn (string $column, ?string $status = null) => Ticket::query()
            ->where($column, '>=', $from)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->selectRaw("date_trunc('{$unit}', {$column})::date as bucket, count(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket')
            ->mapWithKeys(fn ($total, $bucket) => [date($key, strtotime($bucket)) => (int) $total]);

        $in = $count('created_at');
        $done = $count('actual_completion_date', 'completed');

        return collect(CarbonPeriod::create($from, $monthly ? '1 month' : '1 day', today()))
            ->map(fn ($date) => [
                'periode' => $date->format($key),
                'label' => $date->translatedFormat($monthly ? 'M Y' : 'j M'),
                'masuk' => $in->get($date->format($key), 0),
                'selesai' => $done->get($date->format($key), 0),
            ])
            ->values()
            ->all();
    }

    /** Per-service workload and timeliness, busiest first. */
    public static function perService(?CarbonInterface $from = null, int $limit = 10): array
    {
        return Ticket::query()
            ->join('services', 'services.id', '=', 'tickets.service_id')
            ->when($from, fn ($q) => $q->where('tickets.created_at', '>=', $from))
            ->groupBy('services.id', 'services.name', 'services.processing_time')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit($limit)
            ->get([
                'services.name',
                'services.processing_time',
                DB::raw('count(*) as total'),
                DB::raw("count(*) filter (where tickets.status = 'completed') as completed"),
                DB::raw("count(*) filter (where tickets.status in ('submitted','verified','in_process','approved') and tickets.estimated_completion_date < current_date) as overdue"),
                DB::raw("round(avg(tickets.actual_completion_date - tickets.created_at::date) filter (where tickets.status = 'completed'), 1) as avg_days"),
            ])
            ->map(fn ($row) => $row->toArray())
            ->all();
    }

    /**
     * SKM (IKM) and SPAK (IPAK) indexes on a 0-100 scale: average unsur score
     * (1-4) x 25, as in Permenpan RB 14/2017.
     */
    public static function survey(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $average = fn (string $type) => SurveyAnswer::query()
            ->whereNotNull('rating_value')
            ->whereHas('question', fn ($q) => $q->where('type', $type))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->avg('rating_value');

        $skm = $average('skm');
        $spak = $average('spak');
        $ikm = $skm ? round($skm * 25, 2) : null;
        $ipak = $spak ? round($spak * 25, 2) : null;

        return [
            'ikm' => $ikm,
            'ikm_grade' => self::grade($ikm),
            'ipak' => $ipak,
            'ipak_grade' => self::grade($ipak),
            'respondents' => SurveyResponse::query()
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->count(),
        ];
    }

    /** Human-readable completion time, e.g. "2,5 hari" or "< 1 hari". */
    public static function days(float|string|null $days): string
    {
        return match (true) {
            $days === null => '–',
            (float) $days < 1 => '< 1 hari',
            default => str_replace('.', ',', (string) round((float) $days, 1)) . ' hari',
        };
    }

    /** Service quality grade (mutu pelayanan) for an index on a 0-100 scale. */
    public static function grade(?float $index): ?string
    {
        return match (true) {
            $index === null => null,
            $index >= 88.31 => 'A (Sangat Baik)',
            $index >= 76.61 => 'B (Baik)',
            $index >= 65.00 => 'C (Kurang Baik)',
            default => 'D (Tidak Baik)',
        };
    }

    /** Complaint (Dumas) and whistleblowing follow-up. */
    public static function complaints(?CarbonInterface $from = null): array
    {
        $counts = Complaint::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->select('complaint_type', 'status', DB::raw('count(*) as total'))
            ->groupBy('complaint_type', 'status')
            ->get();

        $sum = fn (array $types, array $statuses) => (int) $counts
            ->whereIn('complaint_type', $types)->whereIn('status', $statuses)->sum('total');

        $summary = function (array $types) use ($sum) {
            $all = $sum($types, ['submitted', 'in_review', 'in_progress', 'resolved', 'closed']);
            $done = $sum($types, ['resolved', 'closed']);

            return [
                'total' => $all,
                'new' => $sum($types, ['submitted']),
                'in_progress' => $sum($types, ['in_review', 'in_progress']),
                'resolved' => $done,
                'resolution_rate' => $all ? (int) round($done / $all * 100) : null,
            ];
        };

        return [
            'dumas' => $summary(['complaint', 'suggestion']),
            'whistleblowing' => $summary(['whistleblowing']),
        ];
    }

    /** Guest book (Modul 1). */
    public static function visitors(): array
    {
        return [
            'today' => Visitor::whereDate('check_in_time', today())->count(),
            'active' => Visitor::whereDate('check_in_time', today())->whereNull('check_out_time')->count(),
            'month' => Visitor::where('check_in_time', '>=', now()->startOfMonth())->count(),
        ];
    }
}
