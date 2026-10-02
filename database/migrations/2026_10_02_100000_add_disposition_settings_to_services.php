<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan disposisi per layanan: which back-office units receive the
 * disposition and whether the service document should be signed with a wet
 * signature (TTD) or an electronic one (TTE). Who decides (the disposition
 * mode) stays in approval_required + approval_roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->jsonb('disposition_roles')->nullable()->after('approval_users');
            $table->string('signature_recommendation', 10)->nullable()->after('disposition_roles');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['disposition_roles', 'signature_recommendation']);
        });
    }
};
