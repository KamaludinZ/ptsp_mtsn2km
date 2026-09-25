<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul 8: when approving, the leader chooses how the service product is
 * signed, either an electronic signature (TTE) or a wet signature (TTD).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('signature_type', 10)->nullable()->after('approval_notes');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('signature_type');
        });
    }
};
