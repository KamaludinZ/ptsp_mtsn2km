<?php

namespace App\Support;

use App\Models\PersuratanMaster;
use Illuminate\Support\Str;

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

    /**
     * Active classifications as code => "code — name", for the classification picker.
     *
     * @return array<string, string>
     */
    public static function klasifikasiOptions(): array
    {
        return PersuratanMaster::ofType('klasifikasi')->whereNotNull('kode')->get()
            ->mapWithKeys(fn (PersuratanMaster $m) => [$m->kode => $m->kode . ' — ' . $m->nama])
            ->all();
    }

    /** Lists that collect hand-typed values (classifications need a code and name, so they are kept by hand). */
    public const REMEMBERED = ['tujuan_naskah', 'jenis_surat', 'tembusan', 'instruksi_disposisi'];

    /**
     * Keep values typed by hand as inactive choices in Master Persuratan, so
     * Tata Usaha can review them and switch on the routine ones. A value
     * already in the list (even one switched off on purpose) is left alone.
     *
     * @param  array<string, mixed>  $values  type => value (a string, or lines/array for tembusan)
     */
    public static function rememberManual(array $values): void
    {
        foreach (array_intersect_key($values, array_flip(self::REMEMBERED)) as $type => $value) {
            $names = collect(is_array($value) ? $value : preg_split('/\R/', (string) $value))
                ->map(fn ($name) => Str::squish((string) $name))
                ->filter(fn (string $name) => mb_strlen($name) >= 3 && mb_strlen($name) <= 255)
                ->unique(fn (string $name) => mb_strtolower($name));

            foreach ($names as $name) {
                $known = PersuratanMaster::where('type', $type)->whereRaw('lower(trim(nama)) = ?', [mb_strtolower($name)])->exists();

                if (! $known) {
                    PersuratanMaster::create(['type' => $type, 'nama' => $name, 'is_active' => false, 'sort' => 9999]);
                }
            }
        }
    }
}
