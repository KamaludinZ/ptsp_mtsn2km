<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan {{ $data['label'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        .head { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 6px; margin-bottom: 12px; }
        .head h1 { font-size: 14px; margin: 0; text-transform: uppercase; }
        .head p { margin: 2px 0 0; }
        h2 { font-size: 12px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #e5e7eb; }
        .num { text-align: right; }
        .summary td { border: none; padding: 2px 6px 2px 0; }
        .big { font-size: 18px; font-weight: bold; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ app_brand_name() }}</h1>
        <p>Laporan {{ $data['title'] }}</p>
        <p class="muted">
            Periode {{ $data['from']->translatedFormat('j F Y') }} – {{ $data['to']->translatedFormat('j F Y') }}{{ $data['edition'] ? ' · Edisi ' . $data['edition'] : '' }}
        </p>
    </div>

    <table class="summary">
        <tr>
            <td>Indeks {{ $data['label'] }}</td>
            <td class="big">{{ $data['index'] !== null ? number_format($data['index'], 2, ',', '.') : '–' }}</td>
            <td>Mutu pelayanan</td>
            <td class="big">{{ $data['grade'] ?? '–' }}</td>
        </tr>
        <tr>
            <td>Rata-rata nilai unsur (1–4)</td>
            <td>{{ $data['average'] ? number_format($data['average'], 3, ',', '.') : '–' }}</td>
            <td>Responden</td>
            <td>{{ $data['respondents'] }} ({{ $data['answers'] }} jawaban)</td>
        </tr>
    </table>

    <h2>Nilai per pertanyaan</h2>
    <table>
        <thead><tr><th style="width:5%">No.</th><th>Pertanyaan</th><th class="num" style="width:12%">Jawaban</th><th class="num" style="width:12%">Rata-rata</th></tr></thead>
        <tbody>
            @forelse ($data['scores'] as $score)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $score['question'] }}</td>
                    <td class="num">{{ $score['total_responses'] }}</td>
                    <td class="num">{{ number_format($score['average_score'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Belum ada jawaban pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Demografi responden</h2>
    <table>
        <thead><tr>@foreach (\App\Support\SurveyReportData::DEMOGRAPHICS as $title)<th>{{ $title }}</th>@endforeach</tr></thead>
        <tbody>
            <tr>
                @foreach (array_keys(\App\Support\SurveyReportData::DEMOGRAPHICS) as $key)
                    <td>
                        @forelse ($data['demographics'][$key] ?? [] as $name => $count)
                            {{ $name }}: {{ $count }}<br>
                        @empty
                            <span class="muted">–</span>
                        @endforelse
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <p class="muted" style="margin-top:14px">Metode Permenpan RB No. 14 Tahun 2017. Dicetak {{ now()->translatedFormat('j F Y H:i') }}.</p>
</body>
</html>
