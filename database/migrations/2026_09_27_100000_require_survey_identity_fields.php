<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Identity fields are mandatory, except the optional service ticket code
     * (walk-in respondents have none). Admins can still toggle each question.
     */
    public function up(): void
    {
        DB::table('survey_questions')
            ->where('type', 'identity')
            ->where('question', '<>', 'Kode Tiket Layanan')
            ->update(['is_required' => true]);
    }

    public function down(): void
    {
        // Intentionally left as is: the previous state was inconsistent.
    }
};
