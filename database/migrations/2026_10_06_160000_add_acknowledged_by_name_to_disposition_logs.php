<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Telah didisposisi oleh": the leader on whose behalf a disposition was
 * marked without a signed file (e.g. instructed by phone, entered by staff).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposition_logs', function (Blueprint $table) {
            $table->string('acknowledged_by_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('disposition_logs', function (Blueprint $table) {
            $table->dropColumn('acknowledged_by_name');
        });
    }
};
