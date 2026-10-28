<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permohonan terkait: the PTSP ticket an SK was asked for (Proses tiket >
 * Minta Nomor SK). Until now only its keterangan named the ticket
 * ("Untuk permohonan PTSP-…"); those SK are linked from that text. The SK
 * stays when the ticket goes (its keterangan still names it).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->after('nama_peminta')->constrained('tickets')->nullOnDelete();
            $table->index('ticket_id');
        });

        // keterangan "Untuk permohonan <ticket_number> (<layanan>)." as RegisterSkNumber writes it.
        DB::statement(<<<'SQL'
            UPDATE surat_keputusan sk SET ticket_id = t.id
            FROM tickets t
            WHERE sk.ticket_id IS NULL
              AND sk.keterangan LIKE 'Untuk permohonan %'
              AND split_part(substring(sk.keterangan FROM 18), ' ', 1) IN (t.ticket_number, t.ticket_number || '.')
            SQL);
    }

    public function down(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_id');
        });
    }
};
