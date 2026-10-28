<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor SK terakhir yang pernah diberikan per tahun. A deleted SK leaves
 * the register, but its number was already handed out (and maybe signed),
 * so the next request continues after it instead of giving it again.
 * Seeded from the SK already recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keputusan_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->primary();
            $table->unsignedSmallInteger('last_number')->default(0);
        });

        DB::statement('ALTER TABLE surat_keputusan_sequences ADD CONSTRAINT surat_keputusan_sequences_last_number_check CHECK (last_number BETWEEN 0 AND 9999)');
        DB::statement('INSERT INTO surat_keputusan_sequences (tahun, last_number) SELECT tahun, MAX(nomor_urut) FROM surat_keputusan GROUP BY tahun');
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keputusan_sequences');
    }
};
