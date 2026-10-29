<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Register SK {{ $recap['tahun'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111827; }
        .head { text-align: center; margin-bottom: 10px; }
        .head h1 { font-size: 13px; margin: 0; text-transform: uppercase; }
        .head p { margin: 2px 0 0; font-size: 10px; }
        h2 { font-size: 11px; margin: 14px 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #6b7280; padding: 3px 5px; vertical-align: top; text-align: left; }
        th { background: #e5e7eb; }
        td.num, th.num { text-align: right; }
        .muted { color: #6b7280; }
        .half { width: 49%; display: inline-block; vertical-align: top; }
    </style>
</head>
<body>
    @include('print.partials.letterhead', ['pdf' => true])
    @php($impersonation = \App\Support\Impersonation::current())
    <div class="head">
        <h1>Rekap Register Surat Keputusan Tahun {{ $recap['tahun'] }}</h1>
        <p class="muted">Dicetak {{ \App\Support\Formats::date(now()) }} {{ now()->format('H:i') }} oleh {{ auth()->user()?->name }}@if ($impersonation) (ganti akun oleh {{ $impersonation['admin_name'] }})@endif</p>
    </div>

    <table>
        <tbody>
            <tr><th style="width: 45%">Nomor SK terbit</th><td class="num">{{ $recap['terbit'] }}</td></tr>
            <tr><th>Nomor terakhir</th><td class="num">{{ $recap['nomor_terakhir'] ?? '–' }}</td></tr>
            <tr><th>Terarsip / menunggu scan</th><td class="num">{{ $recap['terarsip'] }} / {{ $recap['menunggu_scan'] }}</td></tr>
            <tr><th>Menunggu scan lewat {{ \App\Support\SkRegisterRecap::SCAN_DUE_DAYS }} hari</th><td class="num">{{ $recap['scan_terlambat'] }}</td></tr>
            <tr><th>Arsip scan lengkap</th><td class="num">{{ $recap['persen_arsip'] === null ? '–' : $recap['persen_arsip'] . '%' }}</td></tr>
            <tr><th>Rata-rata sampai terarsip</th><td class="num">{{ $recap['rata_hari_arsip'] === null ? '–' : str_replace('.', ',', (string) $recap['rata_hari_arsip']) . ' hari' }}</td></tr>
            <tr><th>SK dihapus</th><td class="num">{{ $recap['dihapus'] }}</td></tr>
            @if ($recap['surat_keluar_sk'])
                <tr><th>Surat keluar berjenis SK</th><td class="num">{{ $recap['surat_keluar_sk'] }}</td></tr>
            @endif
        </tbody>
    </table>

    <h2>Per bulan (menurut tanggal surat)</h2>
    <table>
        <thead><tr><th>Bulan</th><th class="num">Terbit</th><th class="num">Terarsip</th><th class="num">Menunggu scan</th></tr></thead>
        <tbody>
            @foreach ($recap['per_bulan'] as $m)
                <tr><td>{{ $m['nama'] }}</td><td class="num">{{ $m['terbit'] }}</td><td class="num">{{ $m['terarsip'] }}</td><td class="num">{{ $m['terbit'] - $m['terarsip'] }}</td></tr>
            @endforeach
            <tr><th>Total</th><th class="num">{{ $recap['terbit'] }}</th><th class="num">{{ $recap['terarsip'] }}</th><th class="num">{{ $recap['menunggu_scan'] }}</th></tr>
        </tbody>
    </table>

    <div>
        <div class="half">
            <h2>Per jenis SK</h2>
            <table>
                <thead><tr><th>Jenis SK</th><th class="num">Terbit</th><th class="num">Terarsip</th></tr></thead>
                <tbody>
                    @foreach ($recap['per_jenis'] as $j)
                        <tr><td>{{ $j['jenis'] }}@unless ($j['aktif']) <span class="muted">(nonaktif)</span>@endunless</td><td class="num">{{ $j['terbit'] }}</td><td class="num">{{ $j['terarsip'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="half" style="margin-left: 1.5%">
            <h2>Per petugas pencatat</h2>
            <table>
                <thead><tr><th>Petugas</th><th class="num">Terbit</th><th class="num">Terarsip</th></tr></thead>
                <tbody>
                    @forelse ($recap['per_petugas'] as $p)
                        <tr><td>{{ $p['petugas'] }}@if ($p['peran'])<br><span class="muted">{{ $p['peran'] }}</span>@endif</td><td class="num">{{ $p['terbit'] }}</td><td class="num">{{ $p['terarsip'] }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="muted">Belum ada SK.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
