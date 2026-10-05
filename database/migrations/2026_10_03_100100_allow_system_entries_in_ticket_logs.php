<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status changes made outside the service flow (console, imports) are also
 * recorded, without a signed-in actor ("Sistem"); a deleted user's entries
 * stay in the history instead of being cascaded away.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->unsignedBigInteger('performed_by')->nullable()->change();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->unsignedBigInteger('performed_by')->nullable(false)->change();
            $table->foreign('performed_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
