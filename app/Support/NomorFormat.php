<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Format nomor surat keluar dari token, mis. "{KS}/{N}/{S}/{k}/{M}/{Y}" →
 * "B/12/MTsN2KM/PP.00/10/2026". Dipakai editor Penomoran Otomatis (contoh
 * nomor) dan, nantinya, perakit nomor di SuratKeluarService.
 */
class NomorFormat
{
    /** Format bawaan bila suatu jenis surat belum diatur (sama dengan penomoran lama). */
    public const DEFAULT = 'B-{N}/{S}/{k}/{M}/{Y}';

    /** Dua pilihan cepat di editor. */
    public const QUICK_FORMATS = [
        '{KS}/{N}/{S}/{k}/{M}/{Y}',
        '{N}/{S}/{k}/{M}/{Y}',
    ];

    /**
     * Token yang dikenali: arti dan contoh untuk Panduan Token.
     *
     * @var array<string, array{label: string, description: string, example: string}>
     */
    public const TOKENS = [
        'KS' => ['label' => 'Kode surat', 'description' => 'Kode sifat/jenis surat, mis. B untuk surat biasa.', 'example' => 'B'],
        'N' => ['label' => 'Nomor urut', 'description' => 'Nomor urut tahunan dari register (1–9999), diambil otomatis.', 'example' => '12'],
        'S' => ['label' => 'Singkatan unit kerja', 'description' => 'Kode satker dari Pengaturan Aplikasi, atau singkatan khusus jenis surat ini.', 'example' => 'MTsN2KM'],
        'k' => ['label' => 'Klasifikasi arsip', 'description' => 'Kode klasifikasi arsip seperti tertulis, mis. PP.00. Bila petugas tidak memilih, dipakai klasifikasi arsip jenis surat; kosong bila keduanya tidak ada.', 'example' => 'PP.00'],
        'K' => ['label' => 'Klasifikasi arsip (huruf besar)', 'description' => 'Kode klasifikasi arsip dalam huruf besar.', 'example' => 'PP.00'],
        'v' => ['label' => 'Variabel tambahan', 'description' => 'Keterangan tambahan yang diisi petugas saat minta nomor, seperti diketik.', 'example' => 'ix-a'],
        'V' => ['label' => 'Variabel tambahan (huruf besar)', 'description' => 'Keterangan tambahan dalam huruf besar.', 'example' => 'IX-A'],
        'M' => ['label' => 'Bulan', 'description' => 'Bulan tanggal surat, angka Arab (10) atau Romawi (X) sesuai pilihan jenis surat.', 'example' => '10'],
        'Y' => ['label' => 'Tahun', 'description' => 'Tahun tanggal surat, empat digit.', 'example' => '2026'],
    ];

    /** Why a format cannot be used, or null when it can (editor and API share this). */
    public static function problem(?string $format): ?string
    {
        $format = trim((string) $format);

        return match (true) {
            $format === '' => 'Format nomor wajib diisi.',
            mb_strlen($format) > 120 => 'Format nomor paling panjang 120 karakter.',
            (bool) self::unknownTokens($format) => 'Token tidak dikenal: ' . collect(self::unknownTokens($format))->map(fn ($t) => '{' . $t . '}')->join(', ') . '.',
            ! in_array('N', self::tokens($format), true) => 'Format wajib memuat {N} agar setiap nomor berbeda.',
            default => null,
        };
    }

    /** @return list<string> tokens in the format, in order, e.g. ['KS', 'N', 'S'] */
    public static function tokens(string $format): array
    {
        preg_match_all('/\{([A-Za-z]{1,2})\}/', $format, $matches);

        return $matches[1];
    }

    /** @return list<string> tokens in the format that are not recognised */
    public static function unknownTokens(string $format): array
    {
        return array_values(array_unique(array_filter(self::tokens($format), fn (string $t) => ! array_key_exists($t, self::TOKENS))));
    }

    /**
     * Rakit nomor dari format dan nilai. Nilai yang tidak diberikan memakai
     * contoh token (untuk pratinjau); token tak dikenal dibiarkan apa adanya.
     *
     * @param  array{KS?: string, N?: int|string, S?: string, k?: ?string, v?: ?string, tanggal?: CarbonInterface, mode_bulan?: string}  $values
     */
    public static function render(string $format, array $values = []): string
    {
        $date = $values['tanggal'] ?? now();
        $month = ($values['mode_bulan'] ?? 'arab') === 'romawi' ? self::roman((int) $date->format('n')) : $date->format('m');
        $k = array_key_exists('k', $values) ? (string) $values['k'] : self::TOKENS['k']['example'];
        $v = array_key_exists('v', $values) ? (string) $values['v'] : self::TOKENS['v']['example'];

        $map = [
            'KS' => (string) ($values['KS'] ?? self::TOKENS['KS']['example']),
            'N' => (string) ($values['N'] ?? self::TOKENS['N']['example']),
            'S' => (string) ($values['S'] ?? SuratKeluarNumber::kodeSatker()),
            'k' => $k,
            'K' => mb_strtoupper($k),
            'v' => $v,
            'V' => mb_strtoupper($v),
            'M' => $month,
            'Y' => $date->format('Y'),
        ];

        $number = preg_replace_callback('/\{([A-Za-z]{1,2})\}/', fn ($m) => $map[$m[1]] ?? $m[0], $format);

        // An empty part (e.g. no classification) leaves no double slash behind.
        return trim(preg_replace('#/{2,}#', '/', $number), '/');
    }

    /** Contoh nomor untuk pratinjau editor (nilai contoh, tanggal hari ini). */
    public static function preview(string $format, string $modeBulan = 'arab', ?string $singkatan = null): string
    {
        return self::render($format, array_filter(['mode_bulan' => $modeBulan, 'S' => $singkatan]));
    }

    public static function roman(int $month): string
    {
        return ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$month - 1] ?? (string) $month;
    }
}
