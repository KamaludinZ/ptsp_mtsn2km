<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat layanan: ticket_logs becomes the audit trail of a ticket, so each
 * entry also keeps the document it concerns, structured details (signature
 * model, recipients, instruction, file name) and the actor's IP address.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->foreignId('ticket_file_id')->nullable()->after('notes')->constrained('ticket_files')->nullOnDelete();
            $table->jsonb('metadata')->nullable()->after('ticket_file_id');
            $table->string('ip_address', 45)->nullable()->after('metadata');
            $table->index(['ticket_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropIndex(['ticket_id', 'created_at']);
            $table->dropIndex(['action', 'created_at']);
            $table->dropConstrainedForeignId('ticket_file_id');
            $table->dropColumn(['metadata', 'ip_address']);
        });
    }
};
