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
        Schema::table('survey_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('survey_responses', 'ticket_code')) {
                $table->string('ticket_code')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('survey_responses', 'respondent_email')) {
                $table->string('respondent_email')->nullable()->after('ticket_code');
            }

            // Drop respondent_address if it exists
            if (Schema::hasColumn('survey_responses', 'respondent_address')) {
                $table->dropColumn('respondent_address');
            }
        });

        // Add unique constraint with try-catch to prevent errors if it already exists
        try {
            Schema::table('survey_responses', function (Blueprint $table) {
                $table->unique('ticket_code', 'unique_ticket_survey');
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique constraint with try-catch
        try {
            Schema::table('survey_responses', function (Blueprint $table) {
                $table->dropUnique('unique_ticket_survey');
            });
        } catch (\Exception $e) {
            // Index doesn't exist, skip
        }

        Schema::table('survey_responses', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('survey_responses', 'ticket_code')) {
                $table->dropColumn('ticket_code');
            }
            if (Schema::hasColumn('survey_responses', 'respondent_email')) {
                $table->dropColumn('respondent_email');
            }

            // Add back respondent_address if it doesn't exist
            if (!Schema::hasColumn('survey_responses', 'respondent_address')) {
                $table->text('respondent_address')->nullable();
            }
        });
    }
};
