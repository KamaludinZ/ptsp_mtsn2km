<?php

namespace App\Support;

use Spatie\Activitylog\Models\Activity;

/**
 * Catatan audit import & ekspor akun pengguna (log "audit" yang sudah ada):
 * deskripsi bakunya dan ringkasan singkat untuk tampilan Log & Audit.
 */
class UserAccountAudit
{
    public const IMPORTED = 'Mengimport akun pengguna';

    public const IMPORT_FAILED = 'Import akun pengguna gagal';

    public const EXPORTED = 'Mengekspor data akun pengguna';

    public const DESCRIPTIONS = [self::IMPORTED, self::IMPORT_FAILED, self::EXPORTED];

    /**
     * Rincian satu catatan import/ekspor: judul, ringkasan berlabel, dan baris yang gagal (import).
     *
     * @return array{title: string, summary: array<string, string>, failed: array<int, array{row: int, reason: string}>}|null
     */
    public static function detail(Activity $activity): ?array
    {
        if ($activity->log_name !== 'audit') {
            return null;
        }

        $p = collect($activity->properties);

        if ($activity->description === self::EXPORTED) {
            return [
                'title' => 'Rincian ekspor',
                'summary' => [
                    'Tab' => $p->get('tab') ?: 'Semua',
                    'Jumlah akun' => (string) (int) $p->get('rows'),
                    'Pencarian' => filled($p->get('search')) ? (string) $p->get('search') : '—',
                    'Filter' => self::activeFilters((array) $p->get('filters', [])) ?: '—',
                ],
                'failed' => [],
            ];
        }

        if ($activity->description !== self::IMPORTED) {
            return null;
        }

        $failed = collect($p->get('failed', []))->map(fn ($row) => ['row' => (int) ($row['row'] ?? 0), 'reason' => (string) ($row['reason'] ?? '')])->all();

        return [
            'title' => 'Rincian import',
            'summary' => [
                'Akun dibuat' => (string) (int) $p->get('created'),
                'Baris gagal' => (string) count($failed),
                'Baris dibaca' => (string) ((int) $p->get('created') + count($failed)),
            ],
            'failed' => $failed,
        ];
    }

    /** Filter tabel yang terisi saat ekspor, mis. "Role: back_office; Belum pernah masuk". */
    private static function activeFilters(array $filters): string
    {
        $labels = ['roles' => 'Role', 'user_type' => 'Kategori', 'never_logged_in' => 'Belum pernah masuk', 'is_active' => 'Status'];

        return collect($filters)
            ->map(function ($state, $name) use ($labels) {
                $values = collect(is_array($state) ? $state : [$state])->flatten()->filter(fn ($v) => filled($v) && $v !== false)->values();
                if ($values->isEmpty()) {
                    return null;
                }
                $label = $labels[$name] ?? $name;
                if ($name === 'roles') {
                    $values = \Spatie\Permission\Models\Role::whereIn('id', $values)->pluck('name')->map(fn (string $role) => RoleAccess::roleLabel($role));
                }

                return $values->every(fn ($v) => $v === true) ? $label : $label . ': ' . $values->join(', ');
            })
            ->filter()
            ->join('; ');
    }

    /** Mis. "12 akun dibuat · 3 baris gagal" atau "Tab Petugas · 40 akun"; null untuk catatan lain. */
    public static function summary(Activity $activity): ?string
    {
        if ($activity->log_name !== 'audit') {
            return null;
        }

        $p = collect($activity->properties);

        return match ($activity->description) {
            self::IMPORTED => (int) $p->get('created') . ' akun dibuat · ' . count($p->get('failed', [])) . ' baris gagal',
            self::IMPORT_FAILED => $p->get('error') ?: 'Berkas tidak dapat dibaca',
            self::EXPORTED => 'Tab ' . ($p->get('tab') ?: 'Semua') . ' · ' . (int) $p->get('rows') . ' akun',
            default => null,
        };
    }
}
