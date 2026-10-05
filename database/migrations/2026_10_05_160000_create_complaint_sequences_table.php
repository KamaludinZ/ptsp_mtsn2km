<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Atomic counters for complaint numbers (PEM/SRN/WSB per month). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_sequences', function (Blueprint $table) {
            $table->string('prefix', 5);
            $table->string('period', 6); // YYYYMM
            $table->unsignedInteger('last_number');
            $table->primary(['prefix', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_sequences');
    }
};
