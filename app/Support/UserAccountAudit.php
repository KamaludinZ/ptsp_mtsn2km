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
