<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kunci permintaan (Idempotency-Key of POST /api/penomoran-sk): a client
 * that sends the same request again, e.g. after a timeout, gets the SK it
 * was already given instead of a second number. Unique per officer, so two
 * requests racing with one key still give one number.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->string('kunci_permintaan', 100)->nullable()->after('peran_pembuat');
        });
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS surat_keputusan_kunci_permintaan_unique ON surat_keputusan (pembuat_id, kunci_permintaan) WHERE kunci_permintaan IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS surat_keputusan_kunci_permintaan_unique');
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropColumn('kunci_permintaan');
        });
    }
};
