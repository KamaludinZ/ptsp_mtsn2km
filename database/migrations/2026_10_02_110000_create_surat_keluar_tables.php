<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Surat keluar: outgoing letter numbers 1-9999 that restart every year.
 * Numbers can be reserved several at once (a batch) and the letter data
 * filled in afterwards, so the descriptive columns are nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->primary();
            $table->unsignedSmallInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('surat_keluar_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedSmallInteger('jumlah_diminta');
            $table->unsignedSmallInteger('nomor_awal');
            $table->unsignedSmallInteger('nomor_akhir');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedSmallInteger('nomor_urut');
            $table->string('nomor_surat')->unique();
            $table->date('tanggal_surat');
            $table->string('tujuan_surat')->nullable();
            $table->string('perihal')->nullable();
            $table->string('jenis_surat', 100)->nullable();
            $table->string('klasifikasi', 50)->nullable();
            $table->text('lampiran')->nullable();
            $table->text('tembusan')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('pembuat_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('surat_keluar_batches')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tahun', 'nomor_urut']);
            $table->index('tanggal_surat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
        Schema::dropIfExists('surat_keluar_batches');
        Schema::dropIfExists('surat_sequences');
    }
};
