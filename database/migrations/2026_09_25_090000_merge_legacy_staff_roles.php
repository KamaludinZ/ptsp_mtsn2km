<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Older seeders created staff roles under other names (tu, petugas_tu,
     * petugas_loket, kepala-sekolah, ...). The application now only checks
     * the canonical names, so move members and permissions of any legacy
     * role onto its canonical role and remove the legacy one.
     */
    private array $map = [
        'tu' => 'back_office',
        'petugas_tu' => 'back_office',
        'petugas-tu' => 'back_office',
        'petugas_loket' => 'front_desk',
        'petugas-loket' => 'front_desk',
        'kepala-sekolah' => 'kepala_sekolah',
        'kepala-tu' => 'kepala_tu',
    ];

    public function up(): void
    {
        foreach ($this->map as $legacy => $canonical) {
            $old = DB::table('roles')->where('name', $legacy)->where('guard_name', 'web')->first();
            if (! $old) {
                continue;
            }

            $newId = DB::table('roles')->where('name', $canonical)->where('guard_name', 'web')->value('id')
                ?? DB::table('roles')->insertGetId([
                    'name' => $canonical,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::statement(
                'INSERT INTO model_has_roles (role_id, model_type, model_id)
                 SELECT ?, model_type, model_id FROM model_has_roles WHERE role_id = ?
                 ON CONFLICT DO NOTHING',
                [$newId, $old->id]
            );
            DB::statement(
                'INSERT INTO role_has_permissions (permission_id, role_id)
                 SELECT permission_id, ? FROM role_has_permissions WHERE role_id = ?
                 ON CONFLICT DO NOTHING',
                [$newId, $old->id]
            );

            DB::table('model_has_roles')->where('role_id', $old->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $old->id)->delete();
            DB::table('roles')->where('id', $old->id)->delete();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Legacy names are no longer used by the application.
    }
};
