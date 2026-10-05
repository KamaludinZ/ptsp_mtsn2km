<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master buku tamu: who a guest can come to see (tujuan) and common reasons
 * for the visit (keperluan). Seeded with the lists the guest book used so far.
 */
return new class extends Migration
{
    public const TUJUAN = [
        'Kepala Madrasah', 'Kepala TU', 'Waka Humas', 'Waka Kurikulum', 'Waka Kesiswaan', 'Waka Sarpras',
        'Komite', 'Unit Tatib', 'Unit UKS', 'Unit BK', 'Mahad/Asrama', 'Wali Kelas', 'Layanan PTSP', 'Lainnya',
    ];

    public const KEPERLUAN = [
        'Konsultasi', 'Mengantar/mengambil berkas', 'Rapat/koordinasi', 'Kunjungan dinas', 'Urusan siswa', 'Lainnya',
    ];

    public function up(): void
    {
        Schema::create('visitor_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // tujuan | keperluan
            $table->string('nama');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['type', 'nama']);
        });

        $now = now();
        $rows = [];
        foreach (['tujuan' => self::TUJUAN, 'keperluan' => self::KEPERLUAN] as $type => $names) {
            foreach ($names as $i => $name) {
                $rows[] = ['type' => $type, 'nama' => $name, 'is_active' => true, 'sort' => $i, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        DB::table('visitor_masters')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_masters');
    }
};
