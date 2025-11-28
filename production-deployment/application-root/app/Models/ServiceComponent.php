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