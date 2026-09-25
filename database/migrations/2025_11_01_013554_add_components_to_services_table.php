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
            $table->text('requirements')->nullable(); // 1. Persyaratan
            $table->text('mechanism')->nullable(); // 2. Sistem, Mekanisme dan Prosedur
            $table->string('processing_time')->nullable(); // 3. Waktu Penyelesaian
            $table->decimal('fee', 10, 2)->nullable(); // 4. Biaya / Tarif
            $table->text('product')->nullable(); // 5. Produk Pelayanan
            $table->text('complaint_handling')->nullable(); // 6. Pengaduan Pelayanan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'requirements',
                'mechanism',
                'processing_time',
                'fee',
                'product',
                'complaint_handling'
            ]);
        });
    }
};
