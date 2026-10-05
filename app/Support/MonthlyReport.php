<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\Visitor;
use Illuminate\Support\Carbon;

/**
 * Laporan bulanan / kinerja layanan: one calendar month of requests
 * (volume, completion, timeliness, status, channel, daily intake),
 * satisfaction, complaints and visitors, compared with the month before.
 */
class MonthlyReport
{
    /** Months offered in the period picker (newest first). */
    public const MONTHS_BACK = 24;

    /** "2026-09" (or null for this month) -> first day of that month; never in the future. */
    public static function month(?string $month): Carbon
    {
        $first = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $month)
            ? Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay()
            : now()->startOfMonth();

        return $first->isAfter(now()) ? now()->startOfMonth() : $first;
    }

    public const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** "Oktober 2026" without relying on the app locale. */
    public static function label(Carbon $month): string
    {
        return self::MONTH_NAMES[$month->month] . ' ' . $month->year;
    }

    /** @return array<int, int> years with data (at least the last two), newest first */
    public static function years(): array
    {
        $first = (int) (Ticket::withTrashed()->min(\Illuminate\Support\Facades\DB::raw('extract(year from created_at)')) ?: now()->year);

        return range(now()->year, min($first, now()->year - 1));
    }

    /** @return array<string, string> "2026-10" => "Oktober 2026", newest first */
    public static function monthOptions(): array
    {
        return collect(range(0, self::MONTHS_BACK - 1))
            ->mapWithKeys(function (int $back) {
                $month = now()->startOfMonth()->subMonthsNoOverflow($back);

                return [$month->format('Y-m') => self::label($month)];
            })
            ->all();
    }

    public static function build(Carbon $month): array
    {
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();
        $previous = $from->copy()->subMonthNoOverflow();

        $tickets = ServiceMetrics::tickets($from, $to);
        $before = ServiceMetrics::tickets($previous, $previous->copy()->endOfMonth());
        $survey = ServiceMetrics::survey($from, $to);

        return [
            'month' => $from->format('Y-m'),
            'label' => self::label($from),
            'previous_label' => self::label($previous),
            'is_current' => $from->isSameMonth(now()),
            'from' => $from,
            'to' => $to->isAfter(now()) ? now() : $to,
            'tickets' => $tickets,
            'previous' => $before,
            'changes' => [
                'total' => self::change($tickets['total'], $before['total']),
                'completed' => self::change($tickets['completed'], $before['completed']),
                'on_time_rate' => self::change($tickets['on_time_rate'], $before['on_time_rate'], points: true),
                'avg_days' => self::change($tickets['avg_days'], $before['avg_days'], lowerIsBetter: true),
            ],
            'daily' => self::daily($from, $to),
            'services' => self::services($from, $to),
            'survey' => $survey,
            'complaints' => ServiceMetrics::complaints($from, $to),
            'visitors' => Visitor::whereBetween('check_in_time', [$from, $to])->count(),
            'nearest_with_data' => $tickets['total'] ? null : self::nearestMonthWithData($from),
        ];
    }

    /** The latest month before $month that has requests (for the empty state), or null. */
    public static function nearestMonthWithData(Carbon $month): ?Carbon
    {
        $last = Ticket::where('created_at', '<', $month->copy()->startOfMonth())->max('created_at');

        return $last ? Carbon::parse($last)->startOfMonth() : null;
    }

    /**
     * Change against the previous month.
     *
     * @return array{value: float|int, label: string, trend: 'up'|'down'|'flat', good: ?bool}|null
     */
    public static function change(int|float|null $now, int|float|null $before, bool $points = false, bool $lowerIsBetter = false): ?array
    {
        if ($now === null || $before === null) {
            return null;
        }

        $value = $points || ! $before ? round($now - $before, 1) : round(($now - $before) / $before * 100);
        $trend = $value > 0 ? 'up' : ($value < 0 ? 'down' : 'flat');
        $unit = $points ? ' poin' : ($before ? '%' : '');
        $label = $trend === 'flat' ? 'Sama dengan bulan lalu' : (($value > 0 ? '+' : '') . str_replace('.', ',', (string) $value) . $unit . ' dari bulan lalu');

        return [
            'value' => $value,
            'label' => $label,
            'trend' => $trend,
            'good' => $trend === 'flat' ? null : (($trend === 'up') !== $lowerIsBetter),
        ];
    }

    /**
     * Rekap jenis layanan: per service, requests of the month by channel and
     * outcome, timeliness and completion time, plus a total row.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: array<string, mixed>}
     */
    public static function services(Carbon $from, Carbon $to): array
    {
        $rows = Ticket::query()
            ->leftJoin('services', 'services.id', '=', 'tickets.service_id')
            ->whereBetween('tickets.created_at', [$from, $to])
            ->groupBy('services.id', 'services.name')
            ->orderByRaw('count(*) desc')->orderBy('services.name')
            ->toBase()
            ->get(['services.name', ...self::aggregates('tickets')])
            ->map(fn ($row) => (array) $row);

        $totals = collect(self::AGGREGATE_KEYS)
            ->mapWithKeys(fn (string $key) => [$key => $rows->sum(fn ($row) => (float) $row[$key])])
            ->all();

        return [
            'rows' => $rows->map(fn ($row) => self::serviceRow($row['name'] ?? 'Tanpa layanan', $row))->all(),
            'total' => self::serviceRow('Total', $totals),
        ];
    }

    /**
     * Rekap per bulan for one year in a single grouped query: requests,
     * completed, rejected/cancelled, still open, on-time rate and average
     * completion days for each month (months without requests included).
     *
     * @return array{year: int, months: array<int, array<string, mixed>>, total: array<string, mixed>}
     */
    public static function year(int $year): array
    {
        $from = Carbon::create($year, 1, 1)->startOfDay();
        $to = $from->copy()->endOfYear();

        $rows = Ticket::query()
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw("date_trunc('month', created_at)")
            ->toBase()
            ->get([
                \Illuminate\Support\Facades\DB::raw("extract(month from date_trunc('month', created_at))::int as month"),
                ...self::aggregates('tickets'),
            ])
            ->keyBy('month');

        $months = [];
        foreach (range(1, 12) as $number) {
            $row = (array) ($rows[$number] ?? []);
            $months[] = ['month' => $number, 'label' => self::MONTH_NAMES[$number], 'future' => $from->copy()->month($number)->isAfter(now())]
                + self::serviceRow(self::MONTH_NAMES[$number], $row + array_fill_keys(self::AGGREGATE_KEYS, 0));
        }

        $totals = collect(self::AGGREGATE_KEYS)->mapWithKeys(fn (string $key) => [$key => $rows->sum(fn ($row) => (float) $row->{$key})])->all();

        return ['year' => $year, 'months' => $months, 'total' => self::serviceRow('Total', $totals)];
    }

    private const AGGREGATE_KEYS = ['total', 'online', 'offline', 'completed', 'rejected', 'open', 'with_target', 'on_time', 'days_sum'];

    /** The shared aggregate columns of the recaps (one service, one month, …). */
    private static function aggregates(string $table): array
    {
        $raw = fn (string $sql) => \Illuminate\Support\Facades\DB::raw(str_replace('t.', $table . '.', $sql));

        return [
            $raw('count(*) as total'),
            $raw("count(*) filter (where t.mode = 'online') as online"),
            $raw("count(*) filter (where t.mode <> 'online') as offline"),
            $raw("count(*) filter (where t.status = 'completed') as completed"),
            $raw("count(*) filter (where t.status in ('rejected', 'cancelled')) as rejected"),
            $raw("count(*) filter (where t.status in ('submitted', 'verified', 'in_process', 'approved')) as open"),
            $raw("count(*) filter (where t.status = 'completed' and t.estimated_completion_date is not null) as with_target"),
            $raw("count(*) filter (where t.status = 'completed' and t.actual_completion_date <= t.estimated_completion_date) as on_time"),
            $raw("coalesce(sum(t.actual_completion_date - t.created_at::date) filter (where t.status = 'completed'), 0) as days_sum"),
        ];
    }

    private static function serviceRow(string $name, array $row): array
    {
        $completed = (int) $row['completed'];

        return [
            'name' => $name,
            'total' => (int) $row['total'],
            'online' => (int) $row['online'],
            'offline' => (int) $row['offline'],
            'completed' => $completed,
            'rejected' => (int) $row['rejected'],
            'open' => (int) $row['open'],
            'on_time_rate' => (int) $row['with_target'] ? (int) round($row['on_time'] / $row['with_target'] * 100) : null,
            'avg_days' => $completed ? round((float) $row['days_sum'] / $completed, 1) : null,
        ];
    }

    /** @return array<int, array{date: string, day: int, total: int}> every day of the month (up to today) */
    private static function daily(Carbon $from, Carbon $to): array
    {
        $counts = Ticket::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('created_at::date as day, count(*) as total')
            ->groupByRaw('created_at::date')
            ->toBase()
            ->pluck('total', 'day');

        $last = $to->isAfter(now()) ? now() : $to;
        $days = [];
        for ($day = $from->copy(); $day->lte($last); $day->addDay()) {
            $days[] = ['date' => $day->toDateString(), 'day' => $day->day, 'total' => (int) ($counts[$day->toDateString()] ?? 0)];
        }

        return $days;
    }
}
