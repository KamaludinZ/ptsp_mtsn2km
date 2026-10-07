<?php

namespace App\Support;

use App\Models\PersuratanMaster;
use InvalidArgumentException;

/**
 * Pengaturan penomoran per jenis surat, tersimpan pada baris jenis_surat
 * (persuratan_masters: format_nomor, mode_bulan, singkatan_unit_kerja):
 * satu-satunya acuan format nomor surat keluar.
 */
class NomorFormatSettings
{
    /** @return array{format: ?string, mode_bulan: string, singkatan: ?string} */
    public static function for(PersuratanMaster $jenis): array
    {
        return [
            'format' => $jenis->format_nomor ?: null,
            'mode_bulan' => $jenis->mode_bulan ?: 'arab',
            'singkatan' => $jenis->singkatan_unit_kerja ?: null,
        ];
    }

    /** Format yang berlaku: milik jenis surat ini, atau format bawaan. */
    public static function effectiveFormat(PersuratanMaster $jenis): string
    {
        return self::for($jenis)['format'] ?? NomorFormat::DEFAULT;
    }

    /** Token {S}: penimpaan jenis surat ini, atau kode satker dari Pengaturan Aplikasi. */
    public static function effectiveSingkatan(?PersuratanMaster $jenis): string
    {
        return ($jenis ? self::for($jenis)['singkatan'] : null) ?? SuratKeluarNumber::kodeSatker();
    }

    /** Contoh nomor jenis surat ini dengan format, mode bulan, dan singkatannya. */
    public static function preview(PersuratanMaster $jenis): string
    {
        return NomorFormat::preview(self::effectiveFormat($jenis), self::for($jenis)['mode_bulan'], self::effectiveSingkatan($jenis));
    }

    /**
     * Simpan format (null = kembali ke bawaan), mode bulan, dan penimpaan
     * singkatan (null = ikut Pengaturan Aplikasi; dibiarkan bila tidak dikirim).
     *
     * @throws InvalidArgumentException bila bukan jenis surat atau isiannya tidak valid
     */
    public static function save(PersuratanMaster $jenis, ?string $format, string $modeBulan = 'arab', string|false|null $singkatan = false): void
    {
        if ($jenis->type !== 'jenis_surat') {
            throw new InvalidArgumentException('Format penomoran hanya untuk jenis surat.');
        }

        $format = $format === null ? null : trim($format);
        if ($format !== null && ($problem = NomorFormat::problem($format))) {
            throw new InvalidArgumentException($problem);
        }
        if (! array_key_exists($modeBulan, PersuratanMaster::MODE_BULAN)) {
            throw new InvalidArgumentException('Mode bulan: arab atau romawi.');
        }

        $values = [
            // The default written out is the same as no custom format.
            'format_nomor' => $format === NomorFormat::DEFAULT ? null : $format,
            'mode_bulan' => $modeBulan,
        ];

        if ($singkatan !== false) {
            $singkatan = $singkatan === null ? null : trim($singkatan);
            if ($singkatan !== null && ($problem = self::singkatanProblem($singkatan))) {
                throw new InvalidArgumentException($problem);
            }
            $values['singkatan_unit_kerja'] = $singkatan ?: null;
        }

        $jenis->update($values);
    }

    /**
     * Variabel tambahan ({v}/{V}) jenis surat ini, atau null bila tidak ada.
     *
     * @return array{label: string, keterangan: ?string, wajib: bool}|null
     */
    public static function variable(?PersuratanMaster $jenis): ?array
    {
        $v = $jenis?->variabel_nomor;
        if (! is_array($v) || blank($v['label'] ?? null)) {
            return null;
        }

        return [
            'label' => (string) $v['label'],
            'keterangan' => filled($v['keterangan'] ?? null) ? (string) $v['keterangan'] : null,
            'wajib' => (bool) ($v['wajib'] ?? true),
        ];
    }

    /** Whether the format in force puts the variable ({v} or {V}) in the number. */
    public static function usesVariable(PersuratanMaster $jenis): bool
    {
        return (bool) array_intersect(['v', 'V'], NomorFormat::tokens(self::effectiveFormat($jenis)));
    }

    /**
     * Simpan definisi variabel tambahan; label kosong/null menghapusnya.
     *
     * @throws InvalidArgumentException bila bukan jenis surat atau labelnya tidak valid
     */
    public static function saveVariable(PersuratanMaster $jenis, ?string $label, ?string $keterangan = null, bool $wajib = true): void
    {
        if ($jenis->type !== 'jenis_surat') {
            throw new InvalidArgumentException('Variabel tambahan hanya untuk jenis surat.');
        }

        $label = trim((string) $label);
        if ($label !== '' && mb_strlen($label) > 60) {
            throw new InvalidArgumentException('Nama variabel paling panjang 60 karakter.');
        }

        $jenis->update(['variabel_nomor' => $label === '' ? null : [
            'label' => $label,
            'keterangan' => filled($keterangan) ? trim($keterangan) : null,
            'wajib' => $wajib,
        ]]);
    }

    /** Why an abbreviation cannot be used in a number, or null. */
    public static function singkatanProblem(string $singkatan): ?string
    {
        return match (true) {
            mb_strlen($singkatan) > 30 => 'Singkatan paling panjang 30 karakter.',
            (bool) preg_match('#[/{}\s]#u', $singkatan) => 'Singkatan tidak boleh berisi spasi, garis miring, atau kurung kurawal.',
            default => null,
        };
    }
}
