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
        Schema::table('tickets', function (Blueprint $table) {
            // Only add columns if they don't exist to prevent errors during migration
            if (!Schema::hasColumn('tickets', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('tickets', 'survey_sent')) {
                $table->boolean('survey_sent')->default(false)->after('approval_notes');
            }
            if (!Schema::hasColumn('tickets', 'survey_sent_at')) {
                $table->timestamp('survey_sent_at')->nullable()->after('survey_sent');
            }
            if (!Schema::hasColumn('tickets', 'ready_for_pickup')) {
                $table->boolean('ready_for_pickup')->default(false)->after('survey_sent_at')->comment('Document ready for pickup at PTSP');
            }
            if (!Schema::hasColumn('tickets', 'pickup_notified_at')) {
                $table->timestamp('pickup_notified_at')->nullable()->after('ready_for_pickup');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $columns = ['approval_status', 'survey_sent', 'survey_sent_at', 'ready_for_pickup', 'pickup_notified_at'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tickets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
