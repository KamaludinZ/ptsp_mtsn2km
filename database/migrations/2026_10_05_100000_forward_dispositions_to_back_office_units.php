<?php

use App\Support\RoleAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Disposisi diteruskan ke unit Back Office: the units become roles (holders
 * get the back-office area) and each ticket keeps the units it was disposed to.
 */
return new class extends Migration
{
    public function up(): void
    {
        RoleAccess::sync();

        Schema::table('tickets', function (Blueprint $table) {
            $table->jsonb('disposition_recipients')->nullable()->after('signature_type');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('disposition_recipients');
        });
    }
};
