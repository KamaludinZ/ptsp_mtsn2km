<?php

namespace App\Support;

use App\Models\PersuratanMaster;

/**
 * Routine choices for letters (persuratan). Offered as suggestions; officers
 * may always type their own value. The lists live in Master Persuratan; the
 * constants below are its initial content.
 */
class Persuratan
{
    public const JENIS_SURAT = [
        'Surat Dinas',
        'Undangan',
        'Surat Keterangan',
        'Surat Tugas',
        'Surat Edaran',
        'Nota Dinas',
        'Pengumuman',
        'Laporan',
        'Rekomendasi',
    ];

    public const KLASIFIKASI = [
        'PP.00' => 'PP.00 — Pendidikan',
        'KS.00' => 'KS.00 — Kesiswaan',
        'KP.00' => 'KP.00 — Kepegawaian',
        'KU.00' => 'KU.00 — Keuangan',
        'HM.00' => 'HM.00 — Kehumasan',
        'OT.00' => 'OT.00 — Organisasi & Tata Laksana',
        'PS.00' => 'PS.00 — Pengawasan',
    ];

    public const TUJUAN_NASKAH = [
        'Kepala Kantor Kementerian Agama Kota Malang',
        'Kepala Dinas Pendidikan Kota Malang',
        'Orang tua/wali siswa',
        'Dewan guru dan tenaga kependidikan',
    ];

    public const TEMBUSAN = [
        'Kepala Madrasah',
        'Kepala Tata Usaha',
        'Arsip',
    ];

    /**
     * Active choices of one master list, as the values the forms store.
     *
     * @return array<int, string>
     */
    public static function options(string $type): array
    {
        return PersuratanMaster::ofType($type)->get()->map(fn (PersuratanMaster $m) => $m->value)->unique()->values()->all();
    }
}
