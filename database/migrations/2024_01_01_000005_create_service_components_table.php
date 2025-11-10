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
        Schema::create('service_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->enum('component_type', [
                'dasar_hukum', 'persyaratan', 'mekanisme', 'jangka_waktu', 
                'biaya', 'produk_layanan', 'sarana_prasarana', 'kompetensi_pelaksana', 
                'pengawasan_internal', 'penanganan_pengaduan', 'jumlah_pelaksana', 
                'jaminan_pelayanan', 'jaminan_keamanan', 'evaluasi_kinerja'
            ]);
            $table->text('content');
            $table->timestamps();
            
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_components');
    }
};