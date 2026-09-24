<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Instansi" is optional on the public visitor book form, but the column
     * was NOT NULL, so every visitor who left it blank got a server error.
     */
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('institution')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('institution')->nullable(false)->change();
        });
    }
};
