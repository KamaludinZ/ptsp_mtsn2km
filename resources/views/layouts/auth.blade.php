<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', app_brand_name())</title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#14532d">

    {{-- Vite Assets: Bootstrap, Font Awesome, AOS, Tailwind (Local - No CDN) --}}
    @if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning())
        @vite([
            'resources/css/bootstrap-custom.css',
            'resources/css/fontawesome.css',
            'resources/js/bootstrap-bundle.js',
            'resources/css/app.css',
            'resources/js/app.js'
        ])
    @else
        {!! App\Helpers\AssetHelper::css('resources/css/bootstrap-custom.css') !!}
        {!! App\Helpers\AssetHelper::css('resources/css/fontawesome.css') !!}
        {!! App\Helpers\AssetHelper::js('resources/js/bootstrap-bundle.js', false) !!}
        {!! App\Helpers\AssetHelper::css('resources/css/app.css') !!}
        {!! App\Helpers\AssetHelper::js('resources/js/app.js', false) !!}
    @endif

    <!-- Custom Styles -->
    <style>
        :root {
            --bs-primary: #14532d;
            --bs-primary-dark: #052e16;
            --bs-primary-light: #166534;
            --bs-primary-rgb: 20, 83, 45;

            --bs-secondary: #ea580c;
            --bs-secondary-dark: #c2410c;
            --bs-secondary-light: #f97316;
            --bs-secondary-rgb: 234, 88, 12;

            --bs-white: #ffffff;
            --bs-gray-50: #f9fafb;
            --bs-gray-100: #f3f4f6;
            --bs-gray-200: #e5e7eb;
            --bs-gray-300: #d1d5db;
            --bs-gray-900: #111827;
            --bs-gray-800: #1f2937;
            --bs-gray-700: #374151;
            --bs-gray-600: #4b5563;

            --bs-focus: #2563eb;
            --bs-text: #1f2937;
            --bs-bg: #ffffff;
            --bs-surface: #f9fafb;
            --bs-border: #e5e7eb;

            --bs-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
        }

        [data-theme="dark"] {
            --bs-text: #f9fafb;
            --bs-bg: #111827;
            --bs-surface: #1f2937;
            --bs-border: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--bs-font-sans);
            background: var(--bs-surface);
            color: var(--bs-text);
            min-height: 100vh;
        }

        [data-theme="dark"] body {
            background: #111827;
        }
    </style>

    <style>
        /* Password visibility toggle (added by the script below to every password input) */
        .pw-field { position: relative; }
        .pw-field .form-control { padding-right: 3rem; }
        .pw-toggle { position: absolute; top: 50%; right: .5rem; transform: translateY(-50%); display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: 0; border-radius: 8px; background: transparent; color: #4b5563; cursor: pointer; }
        .pw-toggle:hover { background: rgba(20,83,45,.08); color: #14532d; }
        .pw-toggle:focus-visible { outline: 2px solid #ea580c; outline-offset: 1px; }
        [data-theme="dark"] .pw-toggle { color: #d1d5db; }
        [data-theme="dark"] .pw-toggle:hover { background: rgba(255,255,255,.08); color: #86efac; }

        /* Auth buttons: one consistent, readable style */
        .form-body .btn, .register-card .btn, .verify-card .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 3rem; padding: 0 1.25rem; font-size: .95rem; font-weight: 600; line-height: 1.2; letter-spacing: 0; text-transform: none; white-space: nowrap; border-radius: 10px; }
        .form-body .btn i, .register-card .btn i, .verify-card .btn i { margin: 0 !important; }
        .form-body .btn-primary, .register-card .btn-primary, .verify-card .btn-primary { background: #166534; border: 1px solid #166534; color: #fff !important; box-shadow: none; }
        .form-body .btn-primary:hover, .register-card .btn-primary:hover, .verify-card .btn-primary:hover { background: #14532d; border-color: #14532d; transform: none; box-shadow: 0 2px 8px rgba(20,83,45,.3); }
        .form-body .btn-outline, .register-card .btn-outline, .verify-card .btn-outline { background: transparent; border: 1px solid #9ca3af; color: #14532d !important; }
        .form-body .btn-outline:hover, .register-card .btn-outline:hover, .verify-card .btn-outline:hover { background: #ecfdf3; border-color: #166534; color: #14532d !important; }
        [data-theme="dark"] .form-body .btn-primary, [data-theme="dark"] .register-card .btn-primary, [data-theme="dark"] .verify-card .btn-primary { background: #22c55e; border-color: #22c55e; color: #052e16 !important; }
        [data-theme="dark"] .form-body .btn-primary:hover, [data-theme="dark"] .register-card .btn-primary:hover, [data-theme="dark"] .verify-card .btn-primary:hover { background: #4ade80; border-color: #4ade80; }
        [data-theme="dark"] .form-body .btn-outline, [data-theme="dark"] .register-card .btn-outline, [data-theme="dark"] .verify-card .btn-outline { border-color: #4b5563; color: #86efac !important; }
        [data-theme="dark"] .form-body .btn-outline:hover, [data-theme="dark"] .register-card .btn-outline:hover, [data-theme="dark"] .verify-card .btn-outline:hover { background: rgba(34,197,94,.14); }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')

    {{-- Bootstrap JS now loaded via Vite (bootstrap-bundle.js) --}}

    <!-- Theme Toggle Script -->
    <script>
        // Theme management
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon();

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const themeIcon = document.getElementById('themeIcon');
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (themeIcon) {
                themeIcon.className = currentTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // Language Toggle
        function changeLanguage(lang) {
            console.log('Language changed to:', lang);
            // Make an AJAX request to change the language
            fetch(`/set-locale/${lang}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    // Reload the page to apply the new language
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error changing language:', error);
            });
        }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            var wrap = document.createElement('div');
            wrap.className = 'pw-field';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pw-toggle';
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.setAttribute('aria-pressed', 'false');
            btn.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                btn.firstChild.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
            wrap.appendChild(btn);
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
