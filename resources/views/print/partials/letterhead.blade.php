{{-- Kop instansi for printed documents and PDFs (table layout: DomPDF has no flexbox). --}}
@php
    $kop = \App\Support\Letterhead::data();
    $logo = $kop['logo'];
    if (($pdf ?? false) && $logo && str_starts_with($logo, url('/'))) {
        $local = public_path(ltrim(parse_url($logo, PHP_URL_PATH), '/'));
        $logo = is_file($local) ? $local : null;
    }
@endphp
<table class="kop" role="presentation">
    <tr>
        <td class="kop-logo-cell">
            @if ($logo)
                <img class="kop-logo" src="{{ $logo }}" alt="Logo {{ $kop['name'] }}">
            @endif
        </td>
        <td class="kop-text">
            @foreach ($kop['lines'] as $line)
                <p class="kop-line">{{ $line }}</p>
            @endforeach
            <p class="kop-name">{{ $kop['name'] }}</p>
            @if ($kop['contact'])
                <p class="kop-contact">{{ $kop['contact'] }}</p>
            @endif
        </td>
        <td class="kop-logo-cell"></td>
    </tr>
</table>
<style>
    table.kop { width: 100%; border-collapse: collapse; border-bottom: 4px double #111827; margin-bottom: 16px; }
    table.kop td { border: none; padding: 0 0 10px; vertical-align: middle; background: none; }
    .kop-logo-cell { width: 80px; }
    .kop-logo { width: 72px; height: 72px; object-fit: contain; }
    .kop-text { text-align: center; }
    .kop-text p { margin: 0; }
    .kop-line { font-size: 13px; text-transform: uppercase; letter-spacing: .02em; }
    .kop-name { font-size: 18px; font-weight: 700; text-transform: uppercase; margin-top: 2px !important; }
    .kop-contact { font-size: 11px; color: #374151; margin-top: 4px !important; }
</style>
