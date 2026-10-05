<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor tiket per prefix and month, handed out atomically (INSERT … ON
 * CONFLICT … RETURNING), so two requests opened at the same moment never get
 * the same number.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sequences', function (Blueprint $table) {
            $table->string('prefix', 20);
            $table->string('period', 6); // YYYYMM
            $table->unsignedInteger('last_number')->default(0);
            $table->primary(['prefix', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sequences');
    }
};
