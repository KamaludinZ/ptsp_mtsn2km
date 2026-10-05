@php
    use App\Support\MonthlyReport;
    use App\Support\ServiceMetrics;
    use App\Support\TicketLabels;

    $t = $report['tickets'];
    $sign = \App\Support\Letterhead::signatory();
    $from = $report['from'];
    $to = $report['to'];
    $period = $from->day . ' ' . MonthlyReport::MONTH_NAMES[$from->month] . ' – ' . $to->day . ' ' . MonthlyReport::MONTH_NAMES[$to->month] . ' ' . $to->year;
    $printed = \App\Support\Formats::date(now());
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan {{ $report['label'] }}</title>
    <style>
        @page { size: A4; margin: 15mm 15mm 18mm; }
        body { font-family: {{ ($pdf ?? false) ? '"DejaVu Serif", serif' : '"Times New Roman", serif' }}; color: #111827; font-size: 12px; margin: 0; padding: 24px; }
        .sheet { max-width: 760px; margin: 0 auto; }
        @if ($pdf ?? false) body { padding: 0; font-size: 10px; } th, td { padding: 3px 4px; } @endif
        h1 { font-size: 15px; text-align: center; text-transform: uppercase; margin: 0; }
        .period { text-align: center; margin: 2px 0 16px; }
        h2 { font-size: 13px; margin: 18px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111827; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .num { text-align: right; white-space: nowrap; }
        tfoot td, tfoot th { font-weight: 700; background: #f3f4f6; }
        .summary td { width: 25%; }
        .summary .value { font-size: 16px; font-weight: 700; }
        .muted { color: #4b5563; font-size: 11px; }
        .empty { border: 1px dashed #6b7280; padding: 12px; text-align: center; }
        .sign { margin: 28px 0 0 auto; width: 260px; text-align: center; page-break-inside: avoid; }
        .sign .space { height: 64px; }
        .toolbar { max-width: 760px; margin: 0 auto 16px; display: flex; gap: 8px; font-family: sans-serif; }
        .toolbar button, .toolbar a { font: inherit; font-size: 13px; padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; color: #111827; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #14532d; color: #fff; border-color: #14532d; }
        @media print { .toolbar { display: none; } body { padding: 0; } th, tfoot td, tfoot th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    @unless ($pdf ?? false)
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak</button>
        <a href="{{ route('reports.monthly.download', ['format' => 'pdf', 'bulan' => $report['month']]) }}">Unduh PDF</a>
        <a href="{{ route('reports.monthly.download', ['format' => 'xlsx', 'bulan' => $report['month']]) }}">Unduh Excel</a>
        <a href="{{ \App\Filament\Pages\Reports\MonthlyReport::getUrl(['bulan' => $report['month']]) }}">Kembali ke laporan</a>
    </div>
    @endunless

    <main class="sheet">
        @include('print.partials.letterhead')

        <h1>Laporan Bulanan Kinerja Layanan PTSP</h1>
        <p class="period">Periode {{ $period }}{{ $report['is_current'] ? ' (bulan berjalan)' : '' }}</p>

        <h2>A. Ringkasan</h2>
        <table class="summary">
            <tr>
                <td>Permohonan masuk<br><span class="value">{{ number_format($t['total'], 0, ',', '.') }}</span><br><span class="muted">{{ $t['online'] }} online · {{ $t['offline'] }} loket</span></td>
                <td>Selesai<br><span class="value">{{ number_format($t['completed'], 0, ',', '.') }}</span><br><span class="muted">{{ $t['completion_rate'] ?? '–' }}% dari permohonan</span></td>
                <td>Tepat waktu<br><span class="value">{{ $t['on_time_rate'] !== null ? $t['on_time_rate'] . '%' : '–' }}</span><br><span class="muted">terhadap standar layanan</span></td>
                <td>Rata-rata penyelesaian<br><span class="value">{{ ServiceMetrics::days($t['avg_days']) }}</span></td>
            </tr>
        </table>
        @if ($report['changes']['total'])
            <p class="muted">Dibandingkan {{ $report['previous_label'] }}: permohonan {{ mb_strtolower($report['changes']['total']['label']) }}.</p>
        @endif

        <h2>B. Rekap Jenis Layanan</h2>
        @if (empty($report['services']['rows']))
            <p class="empty">Tidak ada permohonan pada {{ $report['label'] }}.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width: 28px;">No</th><th>Jenis layanan</th><th class="num">Jumlah</th><th class="num">Online</th><th class="num">Loket</th>
                        <th class="num">Selesai</th><th class="num">Ditolak/batal</th><th class="num">Proses</th><th class="num">Tepat waktu</th><th class="num">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['services']['rows'] as $row)
                        <tr>
                            <td class="num">{{ $loop->iteration }}</td>
                            <td>{{ $row['name'] }}</td>
                            @foreach (['total', 'online', 'offline', 'completed', 'rejected', 'open'] as $key)
                                <td class="num">{{ $row[$key] }}</td>
                            @endforeach
                            <td class="num">{{ $row['on_time_rate'] !== null ? $row['on_time_rate'] . '%' : '–' }}</td>
                            <td class="num">{{ ServiceMetrics::days($row['avg_days']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php($total = $report['services']['total'])
                    <tr>
                        <th colspan="2">Total</th>
                        @foreach (['total', 'online', 'offline', 'completed', 'rejected', 'open'] as $key)
                            <td class="num">{{ $total[$key] }}</td>
                        @endforeach
                        <td class="num">{{ $total['on_time_rate'] !== null ? $total['on_time_rate'] . '%' : '–' }}</td>
                        <td class="num">{{ ServiceMetrics::days($total['avg_days']) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <h2>C. Status Permohonan</h2>
        <table>
            <tr>
                @foreach ($t['by_status'] as $status => $count)
                    <th>{{ TicketLabels::status($status) }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($t['by_status'] as $count)
                    <td class="num">{{ $count }}</td>
                @endforeach
            </tr>
        </table>

        <h2>D. Kepuasan, Pengaduan, dan Kunjungan</h2>
        <table>
            <tr><th style="width: 45%;">Indeks Kepuasan Masyarakat (IKM)</th><td>{{ $report['survey']['ikm'] !== null ? number_format($report['survey']['ikm'], 2, ',', '.') . ' – ' . $report['survey']['ikm_grade'] : 'Belum ada survei' }} ({{ $report['survey']['respondents'] }} responden)</td></tr>
            <tr><th>Pengaduan & saran</th><td>{{ $report['complaints']['dumas']['total'] }} masuk, {{ $report['complaints']['dumas']['resolved'] }} selesai ditindaklanjuti</td></tr>
            <tr><th>Laporan whistleblowing</th><td>{{ $report['complaints']['whistleblowing']['total'] }}</td></tr>
            <tr><th>Kunjungan buku tamu</th><td>{{ $report['visitors'] }}</td></tr>
        </table>

        <div class="sign">
            <p>Malang, {{ $printed }}<br>{{ $sign['title'] }},</p>
            <div class="space"></div>
            <p><strong>{{ $sign['name'] ?? '………………………………' }}</strong>@if (! empty($sign['nip']))<br>NIP. {{ $sign['nip'] }}@endif</p>
        </div>
    </main>
</body>
</html>
