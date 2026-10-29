<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Versi SK: goes up by one with every change of the SK, so a correction
 * made on what another officer has changed in the meantime is refused
 * (optimistic lock) instead of silently overwriting it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->unsignedInteger('versi')->default(1)->after('peran_pembuat');
        });
        DB::statement('ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_versi_check CHECK (versi >= 1)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_versi_check');
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropColumn('versi');
        });
    }
};
