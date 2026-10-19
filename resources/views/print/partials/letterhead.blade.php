{{-- Kop instansi for printed documents and PDFs: the uploaded kop image, else a
     text kop (table layout: DomPDF has no flexbox). --}}
@php
    $kop = \App\Support\Letterhead::data();
    // DomPDF reads images from disk, not over HTTP.
    $isPdf = $pdf ?? false;
    $local = function (?string $url) use ($isPdf) {
        if (! ($isPdf && $url && str_starts_with($url, url('/')))) {
            return $url;
        }
        $path = public_path(ltrim(parse_url($url, PHP_URL_PATH), '/'));

        return is_file($path) ? $path : null;
    };
    $image = $local($kop['image']);
    $logo = $local($kop['logo']);
@endphp
@if ($image)
<div class="kop-image-wrap">
    <img class="kop-image" src="{{ $image }}" alt="Kop {{ $kop['name'] }}">
</div>
@else
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
@endif
<style>
    .kop-image-wrap { margin-bottom: 16px; }
    .kop-image { display: block; width: 100%; height: auto; }
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
