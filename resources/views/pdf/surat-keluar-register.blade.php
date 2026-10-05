<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Register Surat Keluar</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        .head { text-align: center; margin-bottom: 10px; }
        .head h1 { font-size: 13px; margin: 0; text-transform: uppercase; }
        .head p { margin: 2px 0 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #6b7280; padding: 3px 4px; vertical-align: top; text-align: left; }
        th { background: #e5e7eb; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    @include('print.partials.letterhead', ['pdf' => true])
    <div class="head">
        <h1>Buku Register Surat Keluar{{ $years ? ' Tahun ' . $years : '' }}</h1>
        <p class="muted">Dicetak {{ \App\Support\Formats::date(now()) }} {{ now()->format('H:i') }} oleh {{ auth()->user()?->name }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%">No.</th>
                <th style="width: 17%">Nomor Surat</th>
                <th style="width: 9%">Tanggal</th>
                <th style="width: 18%">Tujuan</th>
                <th>Perihal</th>
                <th style="width: 9%">Jenis</th>
                <th style="width: 13%">Tembusan</th>
                <th style="width: 10%">Pembuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($letters as $letter)
                <tr>
                    <td>{{ $letter->nomor_urut }}</td>
                    <td>{{ $letter->nomor_surat }}</td>
                    <td>{{ $letter->tanggal_surat?->format('d-m-Y') }}</td>
                    <td>{{ $letter->tujuan_surat ?? '–' }}</td>
                    <td>{{ $letter->perihal ?? '(belum dilengkapi)' }}</td>
                    <td>{{ $letter->jenis_surat ?? '–' }}</td>
                    <td>{!! nl2br(e($letter->tembusan ?? '–')) !!}</td>
                    <td>{{ $letter->pembuat?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Tidak ada surat keluar pada filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
