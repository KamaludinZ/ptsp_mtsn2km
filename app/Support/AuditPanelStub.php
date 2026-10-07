<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Data tiruan untuk bagian "Perpindahan peran & ganti akun" di tab Log &
 * Audit (Fase 5, frontend). Bentuknya kontrak yang nanti diisi backend dari
 * activity_log dan impersonation_sessions. Saringan:
 * ['cari', 'pengguna', 'peran', 'jenis' (role_switch|impersonation), 'dari', 'sampai'].
 */
class AuditPanelStub
{
    /** @return array{switches_today: int, open_sessions: int, sessions_week: int, impersonated_actions: int} */
    public static function summary(): array
    {
        return ['switches_today' => 7, 'open_sessions' => 1, 'sessions_week' => 4, 'impersonated_actions' => 3];
    }

    /**
     * Rekap aktivitas per pasangan pengguna–peran aktif (7 hari).
     *
     * @return list<array{user: string, role: string, switches: int, sessions: int, ticket_actions: int, impersonated: int, last: Carbon}>
     */
    public static function recap(array $filters = []): array
    {
        $now = now();
        $rows = [
            ['user' => 'Bu Sari', 'role' => 'Kepala Tata Usaha', 'switches' => 4, 'sessions' => 0, 'ticket_actions' => 18, 'impersonated' => 0, 'last' => $now->copy()->subMinutes(5)],
            ['user' => 'Bu Sari', 'role' => 'Front Desk', 'switches' => 3, 'sessions' => 0, 'ticket_actions' => 9, 'impersonated' => 0, 'last' => $now->copy()->subHours(3)],
            ['user' => 'Pak Hadi', 'role' => 'Front Desk', 'switches' => 1, 'sessions' => 0, 'ticket_actions' => 22, 'impersonated' => 0, 'last' => $now->copy()->subHours(2)],
            ['user' => 'Admin PTSP', 'role' => 'Administrator', 'switches' => 0, 'sessions' => 4, 'ticket_actions' => 0, 'impersonated' => 3, 'last' => $now->copy()->subMinutes(42)],
        ];

        return collect($rows)
            ->when(filled($filters['pengguna'] ?? null), fn ($c) => $c->where('user', $filters['pengguna']))
            ->when(filled($filters['peran'] ?? null), fn ($c) => $c->where('role', $filters['peran']))
            ->when(filled($filters['cari'] ?? null), fn ($c) => $c->filter(fn ($r) => str_contains(mb_strtolower($r['user'] . ' ' . $r['role']), mb_strtolower(trim($filters['cari'])))))
            ->values()->all();
    }

    /**
     * @return list<array{at: Carbon, type: string, user: string, description: string, acting_role: ?string}>
     */
    public static function latest(array $filters = []): array
    {
        $now = now();
        $rows = [
            ['at' => $now->copy()->subMinutes(5), 'type' => 'role_switch', 'user' => 'Bu Sari', 'description' => 'Berpindah peran Front Desk → Kepala Tata Usaha', 'acting_role' => 'Kepala Tata Usaha'],
            ['at' => $now->copy()->subMinutes(42), 'type' => 'impersonation', 'user' => 'Admin PTSP', 'description' => 'Mulai ganti akun ke Budi Santoso', 'acting_role' => 'Administrator'],
            ['at' => $now->copy()->subHours(2), 'type' => 'role_switch', 'user' => 'Pak Hadi', 'description' => 'Memilih peran Front Desk saat masuk', 'acting_role' => 'Front Desk'],
            ['at' => $now->copy()->subDays(3), 'type' => 'impersonation', 'user' => 'Admin PTSP', 'description' => 'Mengakhiri ganti akun Sari Wulandari (Kembali ke akun admin)', 'acting_role' => 'Administrator'],
        ];

        return collect($rows)
            ->when(filled($filters['pengguna'] ?? null), fn ($c) => $c->where('user', $filters['pengguna']))
            ->when(filled($filters['peran'] ?? null), fn ($c) => $c->where('acting_role', $filters['peran']))
            ->when(filled($filters['jenis'] ?? null), fn ($c) => $c->where('type', $filters['jenis']))
            ->when(filled($filters['dari'] ?? null), fn ($c) => $c->filter(fn ($r) => $r['at']->gte(Carbon::parse($filters['dari'])->startOfDay())))
            ->when(filled($filters['sampai'] ?? null), fn ($c) => $c->filter(fn ($r) => $r['at']->lte(Carbon::parse($filters['sampai'])->endOfDay())))
            ->when(filled($filters['cari'] ?? null), fn ($c) => $c->filter(fn ($r) => str_contains(mb_strtolower($r['user'] . ' ' . $r['description']), mb_strtolower(trim($filters['cari'])))))
            ->values()->all();
    }

    /** Pilihan saringan pengguna dan peran. */
    public static function options(): array
    {
        $rows = collect(self::recap());

        return [
            'pengguna' => $rows->pluck('user')->unique()->sort()->values()->all(),
            'peran' => $rows->pluck('role')->unique()->sort()->values()->all(),
        ];
    }
}
