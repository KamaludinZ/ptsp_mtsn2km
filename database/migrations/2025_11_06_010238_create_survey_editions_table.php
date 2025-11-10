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
        Schema::create('survey_editions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama edisi (misal: Triwulan I 2025)
            $table->enum('type', ['monthly', 'quarterly', 'yearly']); // Jenis edisi
            $table->string('period'); // Periode (bulan, triwulan, tahun)
            $table->integer('year'); // Tahun
            $table->text('description')->nullable(); // Deskripsi edisi
            $table->date('start_date'); // Tanggal mulai survei
            $table->date('end_date'); // Tanggal selesai survei
            $table->boolean('is_active')->default(false); // Status aktif
            $table->json('settings')->nullable(); // Pengaturan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_editions');
    }
};
