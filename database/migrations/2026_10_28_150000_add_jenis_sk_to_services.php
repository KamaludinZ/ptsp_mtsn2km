<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis SK bawaan per layanan PTSP: a request for "Permohonan Surat Tugas
 * Guru" usually ends in an SK of one jenis. Kept on Master Jenis SK
 * (Layanan PTSP); the SK asked for from such a ticket starts with it.
 * A jenis that is deleted leaves its services without a default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('jenis_sk_id')->nullable()->constrained('persuratan_masters')->nullOnDelete();
            $table->index('jenis_sk_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_sk_id');
        });
    }
};
