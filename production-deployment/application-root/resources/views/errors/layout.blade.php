<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>

    {{-- Vite Assets: Tailwind CSS and Font Awesome (Local - No CDN) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
        }

        .error-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Desktop Layout - Header di sebelah kiri */
        @media (min-width: 768px) {
            .error-card {
                flex-direction: row;
                min-height: 500px;
            }
        }

        .error-header {
            background: linear-gradient(135deg, #15803d 0%, #1a532d 100%);
            color: white;
            padding: 40px 32px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Desktop - Header occupy 35% width */
        @media (min-width: 768px) {
            .error-header {
                width: 35%;
                padding: 60px 40px;
            }
        }

        .error-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #dcfce7;
        }

        .error-header-title {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .error-header-subtitle {
            font-size: 16px;
            font-weight: 300;
            opacity: 0.8;
        }

        .error-content {
            flex: 1;
            padding: 40px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Desktop - Content occupy 65% width */
        @media (min-width: 768px) {
            .error-content {
                width: 65%;
                padding: 60px 50px;
            }
        }

        .error-code {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            color: #1f2937;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 16px;
        }

        .error-message {
            color: #4b5563;
            font-size: 1rem;
            margin-top: 8px;
            max-width: 400px;
        }

        .btn-back {
            display: inline-block;
            background: #166534;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 32px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-back:hover {
            background: #15803d;
            transform: translateY(-2px);
        }

        .support-info {
            margin-top: 32px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            {{-- Bagian Header (Kiri di Desktop) --}}
            <div class="error-header">
                <div class="error-icon-wrapper">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h1 class="error-header-title">{{ config('app.name') }}</h1>
                <p class="error-header-subtitle">Layanan Terpadu Satu Pintu</p>
            </div>

            {{-- Bagian Konten (Kanan di Desktop) --}}
            <div class="error-content">
                <div class="error-code">@yield('code', 'Oops!')</div>
                <h2 class="error-title">@yield('title')</h2>
                <p class="error-message">@yield('message')</p>

                <a href="{{ app('router')->has('home') ? route('home') : url('/') }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Beranda
                </a>

                <div class="support-info">
                    <p>Jika masalah berlanjut, silakan hubungi administrator sistem.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
