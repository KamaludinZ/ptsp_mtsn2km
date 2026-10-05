<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Monitoring Sistem: a snapshot of application and server health every few minutes. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->timestamp('recorded_at')->index();
            $table->boolean('app_up');
            $table->boolean('database_ok');
            $table->decimal('database_latency_ms', 8, 1)->nullable();
            $table->unsignedInteger('queue_pending')->nullable();
            $table->unsignedInteger('queue_failed')->nullable();
            $table->boolean('scheduler_ok');
            $table->decimal('cpu_percent', 5, 1)->nullable();
            $table->decimal('memory_percent', 5, 1)->nullable();
            $table->decimal('disk_percent', 5, 1)->nullable();
            $table->string('status', 10); // success | warning | danger
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
    }
};
