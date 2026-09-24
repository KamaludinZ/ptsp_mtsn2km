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
            $table->string('subject')->nullable()->after('title');
            $table->string('category')->nullable()->after('subject');
            $table->string('reporter_name')->nullable()->after('complainant_name');
            $table->string('reporter_email')->nullable()->after('complainant_email');
            $table->string('reporter_phone')->nullable()->after('complainant_contact');
            $table->unsignedBigInteger('service_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('assigned_to_id')->nullable()->after('assigned_to');
            $table->text('response')->nullable()->after('resolution_notes');
            $table->boolean('is_whistleblowing')->default(false)->after('anonymous');
            $table->string('related_ticket_number')->nullable()->after('complaint_number');
            $table->date('incident_date')->nullable()->after('is_whistleblowing');
            $table->string('incident_location')->nullable()->after('incident_date');
            $table->text('involved_parties')->nullable()->after('incident_location');
            $table->boolean('is_confidential')->default(false)->after('involved_parties');
            $table->text('evidence_files')->nullable()->after('is_confidential');

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
