<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'component_type',
        'content'
    ];

    // Relationship with service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Constants for component types (Permen PANRB 15/2014)
    const DASAR_HUKUM = 'dasar_hukum';
    const PERSYARATAN = 'persyaratan';
    const MEKANISME = 'mekanisme';
    const JANGKA_WAKTU = 'jangka_waktu';
    const BIAYA = 'biaya';
    const PRODUK_LAYANAN = 'produk_layanan';
    const SARANA_PRASARANA = 'sarana_prasarana';
    const KOMPETENSI_PELAKSANA = 'kompetensi_pelaksana';
    const PENGAWASAN_INTERNAL = 'pengawasan_internal';
    const PENANGANAN_PENGADUAN = 'penanganan_pengaduan';
    const JUMLAH_PELAKSANA = 'jumlah_pelaksana';
    const JAMINAN_PELAYANAN = 'jaminan_pelayanan';
    const JAMINAN_KEAMANAN = 'jaminan_keamanan';
    const EVALUASI_KINERJA = 'evaluasi_kinerja';

    /**
     * Komponen standar pelayanan yang tidak punya kolom sendiri di tabel
     * services, sehingga disimpan di service_components dan diisi lewat tab
     * "Standar Pelayanan" di panel /cp. Nilai: [label, petunjuk pengisian].
     */
    public const EXTRA = [
        self::DASAR_HUKUM => ['Dasar hukum', 'Peraturan yang menjadi dasar layanan, mis. undang-undang, PMA, atau keputusan kepala madrasah.'],
        self::SARANA_PRASARANA => ['Sarana dan prasarana', 'Fasilitas yang dipakai, mis. ruang loket PTSP, komputer, printer, aplikasi PTSP.'],
        self::KOMPETENSI_PELAKSANA => ['Kompetensi pelaksana', 'Pengetahuan, keahlian, dan sikap yang harus dimiliki petugas.'],
        self::PENGAWASAN_INTERNAL => ['Pengawasan internal', 'Siapa yang mengawasi pelaksanaan layanan, mis. kepala madrasah dan kepala TU.'],
        self::JUMLAH_PELAKSANA => ['Jumlah pelaksana', 'Jumlah petugas yang melayani, mis. 2 orang petugas TU.'],
        self::JAMINAN_PELAYANAN => ['Jaminan pelayanan', 'Janji layanan kepada pemohon, mis. selesai sesuai jangka waktu standar.'],
        self::JAMINAN_KEAMANAN => ['Jaminan keamanan dan keselamatan', 'Jaminan kerahasiaan data dan keamanan dokumen pemohon.'],
        self::EVALUASI_KINERJA => ['Evaluasi kinerja pelaksana', 'Cara kinerja layanan dievaluasi, mis. survei kepuasan masyarakat (SKM) tiap triwulan.'],
    ];

    public static function getComponentTypes()
    {
        return [
            self::DASAR_HUKUM => 'Dasar Hukum',
            self::PERSYARATAN => 'Persyaratan',
            self::MEKANISME => 'Sistem, Mekanisme, Prosedur',
            self::JANGKA_WAKTU => 'Jangka Waktu Penyelesaian',
            self::BIAYA => 'Biaya/Tarif',
            self::PRODUK_LAYANAN => 'Produk Pelayanan',
            self::SARANA_PRASARANA => 'Sarana, Prasarana',
            self::KOMPETENSI_PELAKSANA => 'Kompetensi Pelaksana',
            self::PENGAWASAN_INTERNAL => 'Pengawasan Internal',
            self::PENANGANAN_PENGADUAN => 'Penanganan Pengaduan',
            self::JUMLAH_PELAKSANA => 'Jumlah Pelaksana',
            self::JAMINAN_PELAYANAN => 'Jaminan Pelayanan',
            self::JAMINAN_KEAMANAN => 'Jaminan Keamanan',
            self::EVALUASI_KINERJA => 'Evaluasi Kinerja',
        ];
    }
}