<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Register Penomoran SK</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        .head { text-align: center; margin-bottom: 10px; }
        .head h1 { font-size: 13px; margin: 0; text-transform: uppercase; }
        .head p { margin: 2px 0 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #6b7280; padding: 3px 4px; vertical-align: top; text-align: left; }
        th { background: #e5e7eb; }
        .muted { color: #6b7280; }
        th.year { background: #f3f4f6; }
    </style>
</head>
<body>
    @include('print.partials.letterhead', ['pdf' => true])
    <div class="head">
        <h1>Buku Register Surat Keputusan{{ $years ? ' Tahun ' . $years : '' }}</h1>
        @php($impersonation = \App\Support\Impersonation::current())
        @if (! empty($criteria))
            <p>Saringan: {{ collect($criteria)->map(fn ($value, $label) => $label . ': ' . $value)->join('; ') }}</p>
        @endif
        <p class="muted">Dicetak {{ \App\Support\Formats::date(now()) }} {{ now()->format('H:i') }} oleh {{ auth()->user()?->name }}@if ($impersonation) (ganti akun oleh {{ $impersonation['admin_name'] }})@endif</p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%">No.</th>
                <th style="width: 12%">Nomor SK</th>
                <th style="width: 9%">Tanggal</th>
                <th>Perihal</th>
                <th style="width: 15%">Jenis SK</th>
                <th style="width: 13%">Peminta</th>
                <th style="width: 9%">Status</th>
                <th style="width: 11%">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @php($perYear = $sk->countBy('tahun'))
            @forelse ($sk as $row)
                {{-- Several years in one report: a heading row where each year starts. --}}
                @if ($perYear->count() > 1 && ($loop->first || $sk[$loop->index - 1]->tahun !== $row->tahun))
                    <tr><th colspan="8" class="year">Tahun {{ $row->tahun }} · {{ $perYear[$row->tahun] }} SK</th></tr>
                @endif
                <tr>
                    <td>{{ $row->nomor_urut }}</td>
                    <td>{{ $row->nomor_sk }}</td>
                    <td>{{ $row->tanggal_surat?->format('d-m-Y') }}</td>
                    <td>Tentang {{ $row->perihal }}@if ($row->keterangan)<br><span class="muted">{{ $row->keterangan }}</span>@endif</td>
                    <td>{{ $row->jenis_sk }}</td>
                    <td>{{ $row->nama_peminta }}</td>
                    <td>{{ \App\Models\SuratKeputusan::STATUS[$row->status()] }}</td>
                    <td>{{ $row->pembuat?->name ?? '–' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Tidak ada SK pada filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
