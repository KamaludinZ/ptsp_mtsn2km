<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Keterangan template: a short how-to-fill instruction shown to applicants next to each template. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->string('petunjuk', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->dropColumn('petunjuk');
        });
    }
};
