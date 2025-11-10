<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PTSP MTsN 2 Kota Malang')</title>

    {{-- SEO Meta Tags --}}
    <meta name="description" content="@yield('description', 'Pelayanan Terpadu Satu Pintu MTsN 2 Kota Malang')">
    <meta name="keywords" content="@yield('keywords', 'PTSP, MTsN 2 Malang, Pelayanan Publik')">

    {{-- Preload critical assets --}}
    {!! assets_preload([
        'resources/css/bootstrap-custom.css',
        'resources/css/app.css',
        'resources/js/bootstrap-bundle.js'
    ]) !!}

    {{-- Load CSS in order --}}
    {!! asset_css('resources/css/bootstrap-custom.css') !!}
    {!! asset_css('resources/css/app.css') !!}

    @stack('styles')
</head>
<body>
    @yield('content')

    {{-- Load JS (deferred for better performance) --}}
    {!! asset_js('resources/js/bootstrap-bundle.js') !!}
    {!! asset_js('resources/js/app.js') !!}

    @stack('scripts')
</body>
</html>
