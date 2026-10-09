<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengingat ganti kata sandi: kapan kata sandi terakhir diganti (pemicu 6 bulan)
 * dan penanda wajib ganti untuk akun hasil import (pemicu pemakaian pertama).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->boolean('must_change_password')->default(false)->after('password_changed_at');
        });

        // Akun lama: umur kata sandinya dihitung sejak akun dibuat.
        DB::table('users')->whereNull('password_changed_at')->update(['password_changed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_changed_at', 'must_change_password']);
        });
    }
};
