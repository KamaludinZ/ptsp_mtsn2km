<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan aplikasi (app_settings):
 * - updated_by: who last changed a setting (kept when that account goes);
 * - keys are lower-case snake/dot names and the type is one the forms know
 *   (existing rows are normalised first; unknown types become text);
 * - the (key, category) index duplicated the unique key: replaced by one on
 *   category, which the settings screens group by.
 */
return new class extends Migration
{
    public const TYPES = ['text', 'textarea', 'email', 'url', 'boolean', 'select', 'image', 'number'];

    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->after('validation_rules')->constrained('users')->nullOnDelete();
        });

        DB::table('app_settings')->whereNotIn('type', self::TYPES)->update(['type' => 'text']);
        DB::table('app_settings')->whereNull('category')->orWhere('category', '')->update(['category' => 'general']);
        foreach (DB::table('app_settings')->get(['id', 'key']) as $row) {
            $key = strtolower(trim($row->key));
            if ($key !== $row->key && ! DB::table('app_settings')->where('key', $key)->exists()) {
                DB::table('app_settings')->where('id', $row->id)->update(['key' => $key]);
            }
        }

        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropIndex(['key', 'category']);
            $table->index('category');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $types = "'" . implode("','", self::TYPES) . "'";
        DB::statement('alter table app_settings drop constraint if exists app_settings_type_check');
        DB::statement("alter table app_settings add constraint app_settings_type_check check (type in ({$types})) not valid");
        DB::statement('alter table app_settings drop constraint if exists app_settings_key_check');
        DB::statement("alter table app_settings add constraint app_settings_key_check check (key ~ '^[a-z0-9][a-z0-9_.]*$') not valid");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table app_settings drop constraint if exists app_settings_type_check');
            DB::statement('alter table app_settings drop constraint if exists app_settings_key_check');
        }

        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->index(['key', 'category']);
            $table->dropConstrainedForeignId('updated_by');
        });
    }
};
