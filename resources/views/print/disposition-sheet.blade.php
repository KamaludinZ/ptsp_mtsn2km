@php
    use App\Support\DispositionHistory;
    use App\Support\Persuratan;
    use App\Support\ServiceDisposition;

    // Every disposition so far (Kepala Madrasah + Kepala TU each dispose): a reprint shows what was really decided, by whom and when.
    $logs = \App\Models\DispositionLog::where('ticket_id', $ticket->id)
        ->whereIn('action', [\App\Enums\DispositionAction::Disposisi, \App\Enums\DispositionAction::Acknowledge])
        ->oldest('id')
        ->get();
    $ticked = $ticket->disposition_recipients
        ?? ($logs->pluck('recipients')->flatten()->filter()->unique()->values()->all() ?: ($ticket->service?->disposition_roles ?? []));
    $chosenInstructions = $logs->pluck('instruction')->filter()->unique()->values()->all();
    $instructions = collect(Persuratan::options('instruksi_disposisi'))->merge($chosenInstructions)->filter()->unique()->values();
    $statuses = DispositionHistory::statuses($ticket);
    $notes = $logs->filter(fn ($log) => filled($log->note))
        ->map(fn ($log) => ($log->role ? \App\Support\RoleAccess::roleLabel($log->role) . ': ' : '') . $log->note);
    $final = $ticket->approval_status === 'approved';
    $waiting = collect($statuses)->where('status', 'pending')->pluck('jabatan');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lembar Disposisi {{ $ticket->ticket_number }}</title>
    <style>
        body { font-family: sans-serif; padding: 24px; color: #111827; font-size: 13px; }
        .sheet { max-width: 720px; margin: 0 auto; }
        h2 { font-size: 15px; text-align: center; letter-spacing: .1em; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #111827; padding: 6px 8px; vertical-align: top; text-align: left; }
        .label { width: 28%; color: #374151; }
        .boxes { list-style: none; margin: 0; padding: 0; columns: 2; }
        .boxes li { margin: 3px 0; }
        .box { display: inline-block; width: 11px; height: 11px; border: 1px solid #111827; margin-right: 6px; text-align: center; line-height: 10px; font-size: 10px; }
        .lines { height: 90px; background: repeating-linear-gradient(transparent, transparent 21px, #9ca3af 22px); }
        .status th { background: #f3f4f6; font-size: 12px; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-disposed { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-skipped { background: #e5e7eb; color: #374151; }
        h3 { font-size: 13px; margin: 18px 0 6px; }
        .legal { margin-top: 18px; padding: 10px 12px; border: 1px dashed #166534; background: #f0fdf4; font-size: 12px; font-style: italic; text-align: center; }
        .legal.pending { border-color: #92400e; background: #fffbeb; }
        .meta { margin-top: 6px; font-size: 11px; color: #6b7280; text-align: center; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="sheet">
        @include('print.partials.letterhead')
        <h2>LEMBAR DISPOSISI</h2>
        <table>
            <tr><td class="label">Nomor tiket</td><td><strong>{{ $ticket->ticket_number }}</strong></td></tr>
            <tr><td class="label">Layanan</td><td>{{ $ticket->service?->name }}</td></tr>
            <tr><td class="label">Pemohon</td><td>{{ $ticket->user?->name }}</td></tr>
            <tr><td class="label">Tanggal diterima</td><td>{{ \App\Support\Formats::date($ticket->created_at) }}</td></tr>
            <tr><td class="label">Perihal</td><td>{{ \Illuminate\Support\Str::limit(strip_tags((string) $ticket->notes), 300) ?: '–' }}</td></tr>
            <tr>
                <td class="label">Diteruskan kepada</td>
                <td>
                    <ul class="boxes">
                        @foreach (ServiceDisposition::RECIPIENTS as $key => $label)
                            <li><span class="box">{{ in_array($key, $ticked, true) ? '✓' : '' }}</span>{{ $label }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="label">Instruksi</td>
                <td>
                    <ul class="boxes">
                        @foreach ($instructions as $instruction)
                            <li><span class="box">{{ in_array($instruction, $chosenInstructions, true) ? '✓' : '' }}</span>{{ $instruction }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="label">Catatan</td>
                <td>
                    @if ($notes->isNotEmpty())
                        @foreach ($notes as $note)
                            <div>{{ $note }}</div>
                        @endforeach
                    @else
                        <div class="lines"></div>
                    @endif
                </td>
            </tr>
        </table>

        <h3>Status Disposisi</h3>
        <table class="status">
            <thead>
                <tr><th>Jabatan</th><th>Nama</th><th>Status disposisi</th><th>Waktu disposisi</th></tr>
            </thead>
            <tbody>
                @foreach ($statuses as $row)
                    <tr>
                        <td>{{ $row['jabatan'] }}</td>
                        <td>{{ $row['name'] ?? '–' }}</td>
                        <td><span class="badge badge-{{ $row['status'] }}">{{ $row['label'] }}</span></td>
                        <td>{{ $row['at'] ? $row['at']->timezone(config('app.timezone'))->translatedFormat('j F Y, H:i') . ' WIB' : '–' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($final)
            <div class="legal">Dokumen ini sah dan telah didisposisi secara elektronik melalui Aplikasi PTSP, tidak memerlukan tanda tangan basah.</div>
        @else
            <div class="legal pending">Disposisi belum lengkap{{ $waiting->isNotEmpty() ? ': menunggu disposisi ' . $waiting->join(' dan ') : '' }}. Dokumen ini belum berlaku sebagai disposisi final.</div>
        @endif
        <p class="meta">{{ $ticket->ticket_number }} · dicetak {{ now()->translatedFormat('j F Y, H:i') }} WIB melalui Aplikasi PTSP</p>
    </div>
    <p class="no-print"><button onclick="window.print()">Cetak / Simpan PDF</button></p>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
