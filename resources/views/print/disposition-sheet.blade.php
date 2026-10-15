@php
    use App\Support\Persuratan;
    use App\Support\RoleAccess;
    use App\Support\ServiceDisposition;

    // The leader's decision, once made: a reprint shows what was really decided, by whom and when.
    $log = \App\Models\DispositionLog::with('actor:id,name')
        ->where('ticket_id', $ticket->id)
        ->whereIn('action', [\App\Enums\DispositionAction::Disposisi, \App\Enums\DispositionAction::Acknowledge])
        ->latest('id')
        ->first();
    $ticked = $log?->recipients ?? $ticket->disposition_recipients ?? $ticket->service?->disposition_roles ?? [];
    $chosenInstruction = $log?->instruction;
    $instructions = collect(Persuratan::options('instruksi_disposisi'))->push($chosenInstruction)->filter()->unique()->values();

    // Signed by the role the leader decided in; before that, the leader printing it (active role), else the service's mode.
    $actingRole = \App\Support\ActiveRoles::inContext(auth()->user());
    $signerRole = $log?->role ?? (in_array($actingRole, ['kepala_sekolah', 'kepala_tu'], true) ? $actingRole : null)
        ?? ($ticket->service?->disposition_mode === 'tu' ? 'kepala_tu' : 'kepala_sekolah');
    $leader = RoleAccess::roleLabel($signerRole);
    $signer = $log ? ($log->acknowledged_by_name ?: $log->actor?->name) : null;
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
        .sign { margin-top: 16px; margin-left: auto; width: 260px; text-align: center; }
        .sign .space { height: 70px; }
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
                            <li><span class="box">{{ $instruction === $chosenInstruction ? '✓' : '' }}</span>{{ $instruction }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="label">Catatan</td>
                <td>
                    @if (filled($log?->note))
                        {{ $log->note }}
                    @else
                        <div class="lines"></div>
                    @endif
                </td>
            </tr>
            @if ($log)
                <tr><td class="label">Tanggal disposisi</td><td>{{ \App\Support\Formats::date($log->created_at) }} · oleh {{ $signer }} ({{ $leader }})</td></tr>
            @endif
            @if ($ticket->service?->signature_recommendation)
                <tr><td class="label">Anjuran tanda tangan</td><td>{{ ServiceDisposition::signature($ticket->service->signature_recommendation) }}</td></tr>
            @endif
        </table>
        <div class="sign">
            <p>Malang, {{ $log ? \App\Support\Formats::date($log->created_at) : '....................................' }}</p>
            <p>{{ $leader }}</p>
            <div class="space"></div>
            <p>({{ $signer ?: '......................................................' }})</p>
        </div>
    </div>
    <p class="no-print"><button onclick="window.print()">Cetak / Simpan PDF</button></p>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
