<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Preload critical assets for better performance --}}
    {!! assets_preload([
        'resources/css/app.css',
        'resources/js/app.js'
    ]) !!}

    {{-- Load CSS --}}
    {!! asset_css('resources/css/app.css') !!}

    {{-- Additional CSS can be added in child views --}}
    @stack('styles')
</head>
<body>
    @include('layouts.navigation')

    <!-- Page Heading -->
    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    {{-- Load JS (deferred) --}}
    {!! asset_js('resources/js/app.js') !!}

    {{-- Additional JS can be added in child views --}}
    @stack('scripts')
</body>
</html>
