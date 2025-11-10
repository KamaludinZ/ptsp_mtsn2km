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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .error-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 600px;
            width: 90%;
            padding: 2rem;
            text-align: center;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: #1f2937;
            margin: 1rem 0;
        }

        .error-message {
            color: #6b7280;
            font-size: 1.125rem;
            margin: 1rem 0 2rem;
        }

        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
        }

        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary {
            display: inline-block;
            background: #f3f4f6;
            color: #4b5563;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            margin-left: 0.5rem;
            transition: background 0.2s;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .error-details {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: left;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .support-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.875rem;
            color: #9ca3af;
        }

        @media (max-width: 640px) {
            .error-code {
                font-size: 4rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .btn-primary, .btn-secondary {
                display: block;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            @yield('content')

            <div class="support-info">
                <p>Jika masalah berlanjut, silakan hubungi administrator sistem.</p>
                <p class="mt-2">
                    <strong>MTsN 2 Kota Malang</strong><br>
                    Email: admin@mtsn2kotamalang.sch.id
                </p>
            </div>
        </div>
    </div>
</body>
</html>
