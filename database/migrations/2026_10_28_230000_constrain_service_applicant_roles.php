<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Layanan untuk peran pemohon: services.user_types_allowed names the
 * applicant roles (App\Support\Applicants::ROLES) a service is open to, none
 * meaning every applicant. The portal's catalogue, its request form and the
 * counter filter by it, so a misspelt role silently hid a service from
 * everyone holding it. Values are tidied (case and spaces, the "wali_murid"
 * spelling, duplicates; an empty list means none), then the database takes
 * known roles only. A role it cannot place stops the migration, naming the
 * service to correct in Kelola Layanan.
 */
return new class extends Migration
{
    private const ROLES = ['siswa', 'walimurid', 'alumni', 'umum', 'instansi', 'guru', 'pegawai'];

    public function up(): void
    {
        DB::statement("UPDATE services SET user_types_allowed = NULL
            WHERE user_types_allowed IS NOT NULL AND jsonb_typeof(user_types_allowed::jsonb) <> 'array'");
        DB::statement(<<<'SQL'
            UPDATE services SET user_types_allowed = (
                SELECT CASE WHEN count(*) = 0 THEN NULL ELSE jsonb_agg(DISTINCT role) END
                FROM (
                    SELECT CASE regexp_replace(lower(trim(value)), '[\s_-]+', '', 'g')
                        WHEN 'walimurid' THEN 'walimurid'
                        WHEN 'instansiperusahaan' THEN 'instansi'
                        ELSE lower(trim(value))
                    END AS role
                    FROM jsonb_array_elements_text(user_types_allowed::jsonb) AS value
                    WHERE trim(value) <> ''
                ) AS roles
            )
            WHERE user_types_allowed IS NOT NULL
        SQL);

        $known = "'" . json_encode(self::ROLES) . "'::jsonb";
        $unknown = DB::table('services')->whereNotNull('user_types_allowed')
            ->whereRaw("NOT (user_types_allowed::jsonb <@ {$known})")
            ->pluck('user_types_allowed', 'name');
        if ($unknown->isNotEmpty()) {
            throw new RuntimeException('Peran pemohon tidak dikenal pada layanan: ' . $unknown->map(fn ($roles, $name) => "{$name} ({$roles})")->join('; ')
                . '. Perbaiki "Dapat diajukan oleh" di Kelola Layanan, lalu jalankan migrasi lagi.');
        }

        DB::statement("ALTER TABLE services ADD CONSTRAINT services_user_types_allowed_check CHECK (
            user_types_allowed IS NULL OR (
                jsonb_typeof(user_types_allowed::jsonb) = 'array'
                AND jsonb_array_length(user_types_allowed::jsonb) > 0
                AND user_types_allowed::jsonb <@ {$known}
            ))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE services DROP CONSTRAINT IF EXISTS services_user_types_allowed_check');
    }
};
