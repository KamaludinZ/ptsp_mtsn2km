<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * At most one survey edition may be active: keep the most recent active
 * one and enforce the rule with a partial unique index.
 */
return new class extends Migration
{
    public function up(): void
    {
        $keep = DB::table('survey_editions')->where('is_active', true)
            ->orderByDesc('start_date')->orderByDesc('id')->value('id');

        if ($keep) {
            DB::table('survey_editions')->where('is_active', true)->where('id', '!=', $keep)
                ->update(['is_active' => false]);
        }

        DB::statement('CREATE UNIQUE INDEX survey_editions_single_active ON survey_editions (is_active) WHERE is_active');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS survey_editions_single_active');
    }
};
