<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monitoring Sistem: CPU used by the PTSP container itself, next to the
 * whole host's load (cpu_percent), so a spike can be told apart: this app,
 * or something else on the same server.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_metrics', function (Blueprint $table) {
            $table->decimal('app_cpu_percent', 5, 1)->nullable()->after('cpu_percent');
        });
    }

    public function down(): void
    {
        Schema::table('system_metrics', function (Blueprint $table) {
            $table->dropColumn('app_cpu_percent');
        });
    }
};
