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
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('approval_required')->default(true)->after('complaint_handling');
            $table->json('approval_roles')->nullable()->after('approval_required'); // Roles that can approve
            $table->json('approval_users')->nullable()->after('approval_roles'); // Specific users that can approve
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'approval_required',
                'approval_roles',
                'approval_users'
            ]);
        });
    }
};
