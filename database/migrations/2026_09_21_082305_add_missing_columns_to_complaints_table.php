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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('subject')->nullable();
            $table->string('category')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->text('response')->nullable();
            $table->boolean('is_whistleblowing')->default(false);
            $table->string('related_ticket_number')->nullable();
            $table->date('incident_date')->nullable();
            $table->string('incident_location')->nullable();
            $table->text('involved_parties')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->text('evidence_files')->nullable();

            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->foreign('assigned_to_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['assigned_to_id']);
            $table->dropColumn([
                'subject', 'category', 'reporter_name', 'reporter_email', 'reporter_phone',
                'service_id', 'assigned_to_id', 'response', 'is_whistleblowing',
                'related_ticket_number', 'incident_date', 'incident_location',
                'involved_parties', 'is_confidential', 'evidence_files',
            ]);
        });
    }
};
