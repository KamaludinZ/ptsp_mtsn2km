<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Rekap Register SK of one year as a workbook: Ringkasan, Per Bulan, Per Jenis SK and Per Petugas sheets. */
class SkRegisterRecapExport implements WithMultipleSheets
{
    /** @param  array<string, mixed>  $recap  App\Support\SkRegisterRecap::build() */
    public function __construct(private array $recap)
    {
    }

    public function sheets(): array
    {
        $r = $this->recap;

        $summary = [
            ['Rekap Register Penomoran SK'],
            ['Tahun', $r['tahun']],
            [''],
            ['Indikator', 'Nilai'],
            ['Nomor SK terbit', $r['terbit']],
            ['Nomor terakhir', $r['nomor_terakhir'] ?? '–'],
            ['Terarsip (scan diunggah)', $r['terarsip']],
            ['Menunggu scan', $r['menunggu_scan']],
            ['Menunggu scan lewat ' . \App\Support\SkRegisterRecap::SCAN_DUE_DAYS . ' hari', $r['scan_terlambat']],
            ['Arsip scan lengkap (%)', $r['persen_arsip']],
            ['Rata-rata sampai terarsip (hari)', $r['rata_hari_arsip']],
            ['SK dihapus', $r['dihapus']],
            ['Surat keluar berjenis SK', $r['surat_keluar_sk']],
        ];

        $months = [
            ['Bulan', 'Terbit', 'Terarsip', 'Menunggu scan'],
            ...array_map(fn (array $m) => [$m['nama'], $m['terbit'], $m['terarsip'], $m['terbit'] - $m['terarsip']], $r['per_bulan']),
            ['Total', $r['terbit'], $r['terarsip'], $r['menunggu_scan']],
        ];

        $jenis = [
            ['Jenis SK', 'Status', 'Terbit', 'Terarsip'],
            ...array_map(fn (array $j) => [$j['jenis'], $j['aktif'] ? 'Aktif' : 'Nonaktif', $j['terbit'], $j['terarsip']], $r['per_jenis']),
        ];

        $officers = [
            ['Petugas pencatat', 'Peran', 'Terbit', 'Terarsip'],
            ...array_map(fn (array $p) => [$p['petugas'], $p['peran'] ?? '–', $p['terbit'], $p['terarsip']], $r['per_petugas']),
        ];

        return [
            self::sheet('Ringkasan', $summary, [1, 4]),
            self::sheet('Per Bulan', $months, [1, count($months)], 'A2'),
            self::sheet('Per Jenis SK', $jenis, [1], 'A2'),
            self::sheet('Per Petugas', $officers, [1], 'A2'),
        ];
    }

    /** @param  array<int, int>  $boldRows */
    private static function sheet(string $title, array $rows, array $boldRows, ?string $freeze = null): object
    {
        // Jenis SK and officer names are typed in: text only (TextValueBinder).
        return new class($title, $rows, $boldRows, $freeze) extends TextValueBinder implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithStrictNullComparison, WithStyles, WithTitle
        {
            public function __construct(private string $title, private array $rows, private array $boldRows, private ?string $freeze)
            {
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function title(): string
            {
                return $this->title;
            }

            public function styles(Worksheet $sheet): array
            {
                if ($this->freeze) {
                    $sheet->freezePane($this->freeze);
                }

                return collect($this->boldRows)->mapWithKeys(fn (int $row) => [$row => ['font' => ['bold' => true]]])->all();
            }
        };
    }

    public static function filename(array $recap, string $extension): string
    {
        return 'rekap-register-sk-' . $recap['tahun'] . '.' . $extension;
    }
}
