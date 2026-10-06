<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peran aktif (konteks kerja) petugas multi-peran di /cp. Spatie tetap
 * menyimpan peran yang dimiliki; kolom ini hanya mengingat peran mana yang
 * terakhir dipilih, supaya bisa dipulihkan ke sesi saat masuk lagi.
 * Peran yang dihapus mengosongkan pilihan (petugas memilih ulang).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_role_id')->nullable()->after('last_login_at')
                ->constrained('roles')->nullOnDelete();
            $table->timestamp('active_role_at')->nullable()->after('active_role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_role_id');
            $table->dropColumn('active_role_at');
        });
    }
};
