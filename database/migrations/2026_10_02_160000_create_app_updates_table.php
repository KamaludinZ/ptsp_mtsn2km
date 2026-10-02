<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Riwayat pembaruan aplikasi: every update requested from Monitoring Sistem. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_updates', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20); // github | upload
            $table->string('from_version', 100)->nullable();
            $table->string('to_version', 100)->nullable();
            $table->string('status', 20)->default('pending'); // pending | applied | failed
            $table->string('package_path')->nullable();
            $table->string('package_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_updates');
    }
};
