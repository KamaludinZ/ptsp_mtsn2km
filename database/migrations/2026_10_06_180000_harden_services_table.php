<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Tabel layanan PTSP (services) for the Katalog Layanan:
 * - every service has a unique slug (the portal finds services by it);
 *   missing ones are generated, duplicates get a suffix;
 * - removing the user who created a service keeps the service (SET NULL);
 * - CHECK constraints on mode, fee and signature recommendation (NOT VALID:
 *   new writes only);
 * - indexes for the active list and the applicant-type filter (jsonb).
 */
return new class extends Migration
{
    private const CHECKS = [
        'services_mode_check' => "mode in ('online','offline','hybrid')",
        'services_fee_check' => 'fee is null or fee >= 0',
        'services_signature_recommendation_check' => "signature_recommendation is null or signature_recommendation in ('ttd','tte')",
    ];

    public function up(): void
    {
        $used = [];
        foreach (DB::table('services')->orderBy('id')->get(['id', 'name', 'slug']) as $service) {
            $base = $service->slug ?: (Str::slug($service->name) ?: 'layanan');
            $slug = $base;
            for ($i = 2; isset($used[$slug]); $i++) {
                $slug = "{$base}-{$i}";
            }
            $used[$slug] = true;
            if ($slug !== $service->slug) {
                DB::table('services')->where('id', $service->id)->update(['slug' => $slug]);
            }
        }

        Schema::table('services', function (Blueprint $table) {
            $table->unique('slug');
            $table->index(['is_active', 'name']);
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('alter table services alter column slug set not null');
        DB::statement('alter table services alter column created_by drop not null');
        DB::statement('alter table services drop constraint if exists services_created_by_foreign');
        DB::statement('alter table services add constraint services_created_by_foreign foreign key (created_by) references users(id) on delete set null');
        DB::statement('create index if not exists services_user_types_allowed_index on services using gin (user_types_allowed)');

        foreach (self::CHECKS as $name => $check) {
            DB::statement("alter table services drop constraint if exists {$name}");
            DB::statement("alter table services add constraint {$name} check ({$check}) not valid");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach (array_keys(self::CHECKS) as $name) {
                DB::statement("alter table services drop constraint if exists {$name}");
            }
            DB::statement('drop index if exists services_user_types_allowed_index');
            DB::statement('alter table services drop constraint if exists services_created_by_foreign');
            DB::statement('alter table services add constraint services_created_by_foreign foreign key (created_by) references users(id) on delete cascade');
            DB::statement('alter table services alter column slug drop not null');
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['is_active', 'name']);
        });
    }
};
