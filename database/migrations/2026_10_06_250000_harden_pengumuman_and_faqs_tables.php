<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Konten publik (Manajemen Konten): pengumuman and FAQ.
 * - pengumumen: an index for the public "tayang" list (active + date window,
 *   newest first) and one for the author; an end date can't be before the
 *   publish date (existing rows that are are repaired: end = publish date);
 * - faqs: order is never negative and question/answer are never blank.
 */
return new class extends Migration
{
    private const CHECKS = [
        'pengumumen' => [
            'pengumumen_end_date_check' => 'end_date is null or end_date >= publish_date',
            'pengumumen_view_count_check' => 'view_count >= 0',
        ],
        'faqs' => [
            'faqs_sort_check' => 'sort >= 0',
            'faqs_question_check' => "btrim(question) <> ''",
            'faqs_answer_check' => "btrim(answer) <> ''",
        ],
    ];

    public function up(): void
    {
        DB::table('pengumumen')->whereNotNull('end_date')->whereColumn('end_date', '<', 'publish_date')
            ->update(['end_date' => DB::raw('publish_date')]);

        Schema::table('pengumumen', function (Blueprint $table) {
            $table->index(['is_active', 'publish_date', 'end_date']);
            $table->index('user_id');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::CHECKS as $table => $checks) {
            foreach ($checks as $name => $check) {
                DB::statement("alter table {$table} drop constraint if exists {$name}");
                DB::statement("alter table {$table} add constraint {$name} check ({$check}) not valid");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach (self::CHECKS as $table => $checks) {
                foreach (array_keys($checks) as $name) {
                    DB::statement("alter table {$table} drop constraint if exists {$name}");
                }
            }
        }

        Schema::table('pengumumen', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'publish_date', 'end_date']);
            $table->dropIndex(['user_id']);
        });
    }
};
