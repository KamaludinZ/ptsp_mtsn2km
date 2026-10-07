<?php

namespace App\Services;

use App\Models\ImpersonationSession;
use App\Models\User;
use App\Support\ActivityTrail;
use App\Support\RoleAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Bagian "Perpindahan peran & ganti akun" di tab Log & Audit: ringkasan,
 * catatan terbaru, rekap per pengguna–peran aktif, dan pilihan saringan,
 * dari rekam jejak yang tidak dapat diubah (activity_log "role",
 * "impersonation", "ticket" dan impersonation_sessions).
 *
 * Saringan: ['cari', 'pengguna' (nama), 'peran' (label), 'jenis'
 * (role_switch|impersonation), 'dari', 'sampai'].
 */
class AuditPanelService
{
    /** Rekap dan catatan "aksi lewat ganti akun" menghitung 7 hari terakhir. */
    public const RECAP_DAYS = 7;

    /** @return array{switches_today: int, open_sessions: int, sessions_week: int, impersonated_actions: int} */
    public function summary(): array
    {
        $week = now()->subDays(self::RECAP_DAYS);

        return [
            'switches_today' => Activity::inLog('role')->where('created_at', '>=', today())->count(),
            'open_sessions' => ImpersonationSession::open()->count(),
            'sessions_week' => ImpersonationSession::where('started_at', '>=', $week)->count(),
            'impersonated_actions' => Activity::query()
                ->where('log_name', '!=', 'impersonation')
                ->where('created_at', '>=', $week)
                ->whereNotNull('properties->impersonated_by')
                ->count(),
        ];
    }

    /**
     * Catatan terbaru perpindahan peran dan ganti akun.
     *
     * @return list<array{at: Carbon, type: string, user: string, description: string, acting_role: ?string}>
     */
    public function latest(array $filters = [], int $limit = 10): array
    {
        $type = $filters['jenis'] ?? null;
        $types = in_array($type, ['role_switch', 'impersonation'], true) ? [$type] : ['role_switch', 'impersonation'];

        return $this->filtered(ActivityTrail::query(), $filters)
            ->whereIn('log_name', array_keys(array_intersect(ActivityTrail::LOGS, $types)))
            ->limit($limit)->get()
            ->map(function (Activity $activity) {
                $row = ActivityTrail::present($activity);

                return [
                    'at' => $row['at'],
                    'type' => $row['type'],
                    'user' => $row['user'],
                    'description' => $row['description'],
                    'acting_role' => $row['acting_role'],
                ];
            })->values()->all();
    }

    /**
     * Rekap per pasangan pengguna–peran aktif dalam RECAP_DAYS hari.
     *
     * @return list<array{user: string, role: string, switches: int, sessions: int, ticket_actions: int, impersonated: int, last: Carbon}>
     */
    public function recap(array $filters = []): array
    {
        $query = $this->filtered(ActivityTrail::query(), $filters)->reorder()
            ->where('created_at', '>=', now()->subDays(self::RECAP_DAYS))
            ->select(
                'causer_id',
                DB::raw("properties->>'peran_aktif' as role"),
                DB::raw("count(*) filter (where log_name = 'role') as switches"),
                DB::raw("count(*) filter (where log_name = 'impersonation' and event = 'started') as sessions"),
                DB::raw("count(*) filter (where log_name = 'ticket') as ticket_actions"),
                DB::raw("count(*) filter (where properties->'impersonated_by' is not null) as impersonated"),
                DB::raw('max(created_at) as last_at'),
            )
            ->groupBy('causer_id', DB::raw("properties->>'peran_aktif'"));

        $names = User::withTrashed()->whereIn('id', (clone $query)->pluck('causer_id')->filter())->pluck('name', 'id');

        return $query->get()
            ->map(fn ($row) => [
                'user' => $names[$row->causer_id] ?? 'Sistem',
                'role' => $row->role ? RoleAccess::roleLabel($row->role) : 'Pemohon',
                'switches' => (int) $row->switches,
                'sessions' => (int) $row->sessions,
                'ticket_actions' => (int) $row->ticket_actions,
                'impersonated' => (int) $row->impersonated,
                'last' => Carbon::parse($row->last_at),
            ])
            ->when(filled($filters['peran'] ?? null), fn (Collection $c) => $c->where('role', $filters['peran']))
            ->sortByDesc('last')->values()->all();
    }

    /** Pilihan saringan pengguna (nama) dan peran (label) yang muncul di rekam jejak. */
    public function options(): array
    {
        $base = Activity::query()->whereIn('log_name', array_keys(ActivityTrail::LOGS));

        return [
            'pengguna' => User::withTrashed()->whereIn('id', (clone $base)->where('causer_type', (new User)->getMorphClass())->select('causer_id'))
                ->orderBy('name')->pluck('name')->unique()->values()->all(),
            'peran' => (clone $base)->whereNotNull('properties->peran_aktif')->distinct()
                ->selectRaw("properties->>'peran_aktif' as role")->pluck('role')
                ->map(fn ($role) => RoleAccess::roleLabel($role))->unique()->sort()->values()->all(),
        ];
    }

    /** Saringan bersama: cari, pengguna (nama), peran (label), dari, sampai. */
    private function filtered($query, array $filters)
    {
        $roleKey = filled($filters['peran'] ?? null)
            ? collect(User::STAFF_ROLES)->first(fn ($r) => RoleAccess::roleLabel($r) === $filters['peran'])
            : null;
        $userIds = filled($filters['pengguna'] ?? null) ? User::withTrashed()->where('name', $filters['pengguna'])->pluck('id') : null;
        $like = filled($filters['cari'] ?? null) ? '%' . addcslashes(trim((string) $filters['cari']), '%_\\') . '%' : null;

        return $query
            ->when($userIds !== null, fn ($q) => $q->where('causer_type', (new User)->getMorphClass())->whereIn('causer_id', $userIds))
            ->when($roleKey, fn ($q) => $q->where('properties->peran_aktif', $roleKey))
            ->when($like, fn ($q) => $q->where(fn ($q) => $q->where('description', 'ilike', $like)
                ->orWhereHasMorph('causer', [User::class], fn ($c) => $c->where('name', 'ilike', $like))))
            ->when(filled($filters['dari'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['dari']))
            ->when(filled($filters['sampai'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['sampai']));
    }
}
