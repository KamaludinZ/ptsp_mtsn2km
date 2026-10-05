<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Tamu - {{ $visitor->name }}</title>
    <style>
        body { font-family: sans-serif; padding: 24px; }
        .pass { max-width: 360px; border: 2px solid #14532d; border-radius: 12px; padding: 20px; }
        .pass h1 { font-size: 18px; margin: 0 0 4px; color: #14532d; }
        .pass p { margin: 4px 0; font-size: 14px; }
        .label { color: #6b7280; font-size: 12px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="pass">
        <h1>Kartu Tamu</h1>
        <p class="label">Nama</p>
        <p>{{ $visitor->name }}</p>
        <p class="label">Institusi</p>
        <p>{{ $visitor->institution }}</p>
        <p class="label">Keperluan</p>
        <p>{{ $visitor->purpose }}</p>
        <p class="label">Bertemu Dengan</p>
        <p>{{ $visitor->person_to_meet ?? '-' }}</p>
        <p class="label">Waktu Check-in</p>
        <p>{{ $visitor->check_in_time ? \App\Support\Formats::date($visitor->check_in_time) . ' ' . $visitor->check_in_time->format('H:i') : '' }}</p>
        @if($visitor->visitor_card_number)
            <p class="label">Nomor Kartu</p>
            <p>{{ $visitor->visitor_card_number }}</p>
        @endif
    </div>
    <p class="no-print"><button onclick="window.print()">Cetak</button></p>
    <script>window.addEventListener("load", () => window.print());</script>
</body>
</html>
