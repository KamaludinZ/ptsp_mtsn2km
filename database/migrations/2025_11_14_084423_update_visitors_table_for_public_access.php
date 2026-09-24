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
        Schema::table('visitors', function (Blueprint $table) {
            // Add phone and notes columns
            $table->string('phone', 20)->nullable();
            $table->text('notes')->nullable();

            // Make person_to_meet nullable for public submissions
            $table->string('person_to_meet')->nullable()->change();

            // Make created_by nullable for public submissions
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Drop added columns
            $table->dropColumn(['phone', 'notes']);

            // Note: Reverting nullable changes would require knowing the original state
            // For safety, we'll leave person_to_meet and created_by as nullable in rollback
        });
    }
};
