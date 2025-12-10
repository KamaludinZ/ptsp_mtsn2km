<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('survey_archives', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'spak' or 'skm'
            $table->string('period')->nullable(); // Monthly period (e.g., '2025-01' for January 2025)
            $table->string('quarter')->nullable(); // Quarter identifier (e.g., '2025-Q1' for Q1 2025)
            $table->integer('year'); // Year (e.g., 2025)
            $table->json('data')->nullable(); // JSON data containing calculated values
            $table->json('calculated_values')->nullable(); // Calculated values array
            $table->boolean('is_quarterly_archive')->default(false); // Flag indicating if this is a quarterly archive
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['type', 'period']);
            $table->index(['type', 'quarter']);
            $table->index(['type', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_archives');
    }
};
