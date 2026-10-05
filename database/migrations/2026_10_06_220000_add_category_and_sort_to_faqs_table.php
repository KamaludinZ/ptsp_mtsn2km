<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** FAQ grouped by category and ordered by hand; existing questions keep their current order (newest first). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('answer');
            $table->unsignedInteger('sort')->default(0)->after('category');
            $table->index(['is_active', 'category', 'sort']);
        });

        foreach (DB::table('faqs')->orderByDesc('created_at')->orderByDesc('id')->pluck('id')->values() as $position => $id) {
            DB::table('faqs')->where('id', $id)->update(['sort' => $position + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'category', 'sort']);
            $table->dropColumn(['category', 'sort']);
        });
    }
};
