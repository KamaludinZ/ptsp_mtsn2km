<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * Rekam jejak aktivitas dari activity_log: perpindahan peran ("role"), sesi
 * ganti akun ("impersonation"), dan aksi tiket dengan konteksnya ("ticket").
 * Satu bentuk baris untuk API dan halaman (jenis: ActivityTrail page TYPES).
 */
class ActivityTrail
{
    /** activity_log.log_name → jenis rekam jejak. */
    public const LOGS = ['role' => 'role_switch', 'impersonation' => 'impersonation', 'ticket' => 'ticket'];

    /**
     * @param  array{pengguna?: ?int, peran?: ?string, jenis?: ?string, aksi?: ?string, dari?: ?string, sampai?: ?string}  $filters
     */
    public static function query(array $filters = []): Builder
    {
        $type = $filters['jenis'] ?? null;
        $logs = $type ? array_keys(self::LOGS, $type, true) : array_keys(self::LOGS);
        $like = filled($filters['aksi'] ?? null) ? '%' . addcslashes((string) $filters['aksi'], '%_\\') . '%' : null;

        return Activity::query()
            ->with('causer')
            ->whereIn('log_name', $logs ?: ['-'])
            ->when($filters['pengguna'] ?? null, fn (Builder $q, $id) => $q->where('causer_type', (new User)->getMorphClass())->where('causer_id', $id))
            ->when($filters['peran'] ?? null, fn (Builder $q, $role) => $q->where('properties->peran_aktif', $role))
            ->when($like, fn (Builder $q) => $q->where(fn (Builder $q) => $q->where('description', 'ilike', $like)->orWhere('event', 'ilike', $like)))
            ->when($filters['dari'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['sampai'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('created_at')->latest('id');
    }

    /**
     * @return array{id: int, at: Carbon, type: string, event: ?string, user: string, user_id: ?int, acting_role: ?string, acting_role_key: ?string, description: string, from_role: ?string, to_role: ?string, ticket: ?string, service: ?string, impersonated_by: ?string, ip: ?string, details: array<string, string>}
     */
    public static function present(Activity $activity): array
    {
        $p = collect($activity->properties);
        $role = $p->get('peran_aktif') ?? ($activity->log_name === 'role' ? $p->get('ke') : null);

        return [
            'id' => $activity->id,
            'at' => $activity->created_at,
            'type' => self::LOGS[$activity->log_name] ?? $activity->log_name,
            'event' => $activity->event,
            'user' => $activity->causer?->name ?? ($p->get('admin') ?? 'Sistem'),
            'user_id' => $activity->causer_id,
            'acting_role' => $role ? RoleAccess::roleLabel($role) : null,
            'acting_role_key' => $role,
            'description' => $activity->description,
            'from_role' => $activity->log_name === 'role' && $p->get('dari') ? RoleAccess::roleLabel($p->get('dari')) : null,
            'to_role' => $activity->log_name === 'role' && $p->get('ke') ? RoleAccess::roleLabel($p->get('ke')) : null,
            'ticket' => $p->get('tiket'),
            'service' => $p->get('layanan'),
            'impersonated_by' => data_get($p->get('impersonated_by'), 'admin'),
            'ip' => $p->get('ip'),
            'details' => self::details($activity->log_name, $p->all()),
        ];
    }

    /** Readable details for the detail view, per kind of entry. */
    private static function details(string $log, array $p): array
    {
        $labels = match ($log) {
            'role' => ['sumber' => 'Sumber', 'halaman' => 'Halaman', 'perangkat' => 'Perangkat'],
            'impersonation' => ['akun_dipakai' => 'Akun dipakai', 'alasan' => 'Alasan', 'cara_berakhir' => 'Cara berakhir', 'keterangan' => 'Keterangan', 'durasi_menit' => 'Durasi (menit)', 'perangkat' => 'Perangkat'],
            'ticket' => ['status_awal' => 'Status awal', 'status_akhir' => 'Status akhir'],
            default => [],
        };

        return collect($labels)
            ->filter(fn ($label, $key) => filled($p[$key] ?? null))
            ->mapWithKeys(fn ($label, $key) => [$label => match ($key) {
                'status_awal', 'status_akhir' => TicketLabels::status($p[$key]),
                'cara_berakhir' => \App\Models\ImpersonationSession::END_REASONS[$p[$key]] ?? $p[$key],
                default => (string) $p[$key],
            }])
            ->all();
    }
}
