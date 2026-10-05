<?php

namespace App\Exports;

use App\Support\MonthlyReport;
use App\Support\ServiceMetrics;
use App\Support\TicketLabels;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Laporan bulanan as a workbook: Ringkasan and Rekap Jenis Layanan sheets. */
class MonthlyReportExport implements WithMultipleSheets
{
    public function __construct(private array $report)
    {
    }

    public function sheets(): array
    {
        $r = $this->report;
        $t = $r['tickets'];

        $summary = [
            ['Laporan Bulanan Kinerja Layanan PTSP'],
            ['Periode', $r['label'] . ($r['is_current'] ? ' (bulan berjalan)' : '')],
            [''],
            ['Indikator', 'Nilai'],
            ['Permohonan masuk', $t['total']],
            ['Permohonan online', $t['online']],
            ['Permohonan loket', $t['offline']],
            ['Selesai', $t['completed']],
            ['Persentase selesai (%)', $t['completion_rate']],
            ['Tepat waktu (%)', $t['on_time_rate']],
            ['Rata-rata penyelesaian', ServiceMetrics::days($t['avg_days'])],
            ['Masih terbuka', $t['open']],
            ['Melewati target', $t['overdue']],
            ['IKM', $r['survey']['ikm']],
            ['Mutu pelayanan', $r['survey']['ikm_grade']],
            ['Responden survei', $r['survey']['respondents']],
            ['Pengaduan & saran', $r['complaints']['dumas']['total']],
            ['Pengaduan selesai', $r['complaints']['dumas']['resolved']],
            ['Laporan whistleblowing', $r['complaints']['whistleblowing']['total']],
            ['Kunjungan buku tamu', $r['visitors']],
            [''],
            ['Status', 'Jumlah'],
            ...collect($t['by_status'])->map(fn ($count, $status) => [TicketLabels::status($status), $count])->values()->all(),
        ];

        $row = fn (array $row, $number = '') => [
            $number, $row['name'], $row['total'], $row['online'], $row['offline'], $row['completed'], $row['rejected'], $row['open'],
            $row['on_time_rate'], $row['avg_days'],
        ];
        $recap = [
            ['No', 'Jenis layanan', 'Jumlah', 'Online', 'Loket', 'Selesai', 'Ditolak/batal', 'Dalam proses', 'Tepat waktu (%)', 'Rata-rata (hari)'],
            ...array_map(fn ($item, $i) => $row($item, $i + 1), $r['services']['rows'], array_keys($r['services']['rows'])),
            $row($r['services']['total']),
        ];

        $daily = [
            ['Tanggal', 'Permohonan masuk'],
            ...array_map(fn (array $day) => [$day['date'], $day['total']], $r['daily']),
            ['Total', array_sum(array_column($r['daily'], 'total'))],
        ];

        return [
            self::sheet('Ringkasan', $summary, [1, 4, 22], freeze: null),
            self::sheet('Rekap Jenis Layanan', $recap, [1, count($recap)], freeze: 'A2'),
            self::sheet('Harian', $daily, [1, count($daily)], freeze: 'A2'),
        ];
    }

    /** @param  array<int, int>  $boldRows */
    private static function sheet(string $title, array $rows, array $boldRows, ?string $freeze): object
    {
        return new class($title, $rows, $boldRows, $freeze) implements FromArray, ShouldAutoSize, WithStrictNullComparison, WithStyles, WithTitle
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

    public static function filename(array $report, string $extension): string
    {
        return 'laporan-bulanan-' . $report['month'] . '.' . $extension;
    }
}
