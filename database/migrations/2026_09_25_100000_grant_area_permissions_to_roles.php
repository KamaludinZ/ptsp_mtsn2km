<?php

use App\Support\RoleAccess;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Area permissions used to be given to individual seeded users only, so
     * staff created later from the admin panel were refused (403), and admin
     * could not open the front desk or back office. Attach them to roles.
     */
    public function up(): void
    {
        RoleAccess::sync();
    }

    public function down(): void
    {
        // Keep permissions; removing them would lock staff out.
    }
};
