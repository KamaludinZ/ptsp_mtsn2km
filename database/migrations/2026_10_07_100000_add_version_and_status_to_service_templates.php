<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Template versions and status: `versi` counts the files uploaded for a
 * template (raised whenever the file is replaced) and `is_active` hides an
 * outdated template from applicants without deleting it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->unsignedInteger('versi')->default(1);
            $table->boolean('is_active')->default(true);
            $table->index(['service_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->dropIndex(['service_id', 'is_active']);
            $table->dropColumn(['versi', 'is_active']);
        });
    }
};
