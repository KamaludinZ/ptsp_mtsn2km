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
        Schema::create('survey_unsur', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama unsur (misal: Prosedur, Pelayanan, dll)
            $table->string('code')->unique(); // Kode unsur (misal: P1, P2)
            $table->text('description')->nullable(); // Deskripsi unsur
            $table->enum('survey_type', ['identity', 'skm', 'spak']); // Jenis survei
            $table->integer('order')->default(0); // Urutan tampilan
            $table->boolean('is_active')->default(true); // Status aktif
            $table->json('metadata')->nullable(); // Metadata tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_unsur');
    }
};
