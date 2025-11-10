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
        Schema::create('registration_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // 10 digit code
            $table->enum('user_type', ['siswa', 'guru', 'pegawai']); // jenis akun civitas
            $table->string('description')->nullable(); // deskripsi kode ini untuk apa
            $table->boolean('is_active')->default(true); // apakah kode masih aktif
            $table->boolean('is_single_use')->default(false); // apakah kode hanya bisa digunakan sekali
            $table->integer('max_uses')->nullable(); // maksimal penggunaan (null = unlimited)
            $table->integer('used_count')->default(0); // jumlah sudah digunakan
            $table->timestamp('expires_at')->nullable(); // tanggal kadaluarsa
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_codes');
    }
};
