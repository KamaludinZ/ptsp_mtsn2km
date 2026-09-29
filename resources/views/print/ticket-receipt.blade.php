<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tanda Terima {{ $ticket->ticket_number }}</title>
    <style>
        body { font-family: sans-serif; padding: 24px; color: #111827; }
        .receipt { max-width: 480px; border: 2px solid #14532d; border-radius: 12px; padding: 20px 24px; }
        .receipt h1 { font-size: 18px; margin: 0 0 2px; color: #14532d; }
        .receipt .org { font-size: 13px; color: #4b5563; margin: 0 0 16px; }
        .number { font-size: 22px; font-weight: 700; letter-spacing: .02em; margin: 2px 0 12px; }
        dl { display: grid; grid-template-columns: 150px 1fr; gap: 6px 12px; margin: 0; font-size: 14px; }
        dt { color: #6b7280; }
        dd { margin: 0; }
        .note { margin-top: 16px; font-size: 12px; color: #4b5563; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="receipt">
        <h1>Tanda Terima Permohonan Layanan</h1>
        <p class="org">{{ app_brand_name() }}</p>
        <div>Nomor tiket</div>
        <div class="number">{{ $ticket->ticket_number }}</div>
        <dl>
            <dt>Pemohon</dt><dd>{{ $ticket->user?->name }}</dd>
            <dt>Layanan</dt><dd>{{ $ticket->service?->name }}</dd>
            <dt>Tanggal</dt><dd>{{ $ticket->created_at->translatedFormat('j F Y, H:i') }}</dd>
            <dt>Perkiraan selesai</dt>
            <dd>
                {{ $ticket->estimated_completion_date?->translatedFormat('j F Y') ?? 'Sesuai standar pelayanan' }}
                @if ($ticket->service?->processing_time)
                    ({{ $ticket->service->processing_time }})
                @endif
            </dd>
        </dl>
        <p class="note">Simpan nomor tiket ini. Status permohonan dapat dilacak di {{ route('onlineportal.track.ticket.form') }}.</p>
    </div>
    <p class="no-print"><button onclick="window.print()">Cetak</button></p>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
