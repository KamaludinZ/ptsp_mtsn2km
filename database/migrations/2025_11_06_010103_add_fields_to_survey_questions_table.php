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
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->string('survey_type')->default('identity')->after('is_active'); // identity, skm, spak
            $table->string('category')->nullable()->after('survey_type'); // untuk kategori unsur
            $table->integer('unsur_id')->nullable()->after('category'); // untuk mengelompokkan ke unsur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            //
        });
    }
};
