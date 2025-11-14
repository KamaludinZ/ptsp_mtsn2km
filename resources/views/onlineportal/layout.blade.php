<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PTSP MTsN 2 Kota Malang') }}</title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#14532d">

    {{-- Vite Assets: Bootstrap, Font Awesome, AOS, Tailwind (Local - No CDN) --}}
    @vite(['resources/css/bootstrap-custom.css', 'resources/css/app.css', 'resources/css/dark-mode.css', 'resources/css/accessibility.css', 'resources/js/bootstrap-bundle.js', 'resources/js/app.js', 'resources/js/accessibility.js'])
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <!-- Floating Action Buttons - Left Side -->
    <div class="floating-action-buttons-left">
        <!-- Accessibility Button -->
        <button id="accessibility-btn"
                class="floating-btn accessibility-btn pulse">
            <span style="line-height: 1;">♿</span>
        </button>

        <!-- Back to Top Button -->
        <button onclick="scrollToTop()"
                id="back-to-top-btn"
                class="floating-btn back-to-top-btn">
            <i class="fas fa-arrow-up"></i>
        </button>

        <!-- WhatsApp Button -->
        <a href="https://wa.me/{{ config('app.whatsapp_number', '6285183367500') }}?text={{ urlencode('Halo, saya ingin bertanya tentang layanan PTSP MTsN 2 KOTA MALANG') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="floating-btn whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Modern Accessibility Panel -->
    <div id="accessibilityPanel" class="accessibility-panel">
        <!-- Panel Header -->
        <div class="accessibility-panel-header">
            <h3 class="accessibility-panel-title">
                <span>🎯</span>
                <span>Pusat Aksesibilitas</span>
            </h3>
            <p class="accessibility-panel-subtitle">WCAG 2.1 AA Compliant • Inklusif untuk Semua</p>
            <button onclick="toggleAccessibilitySidebar()" class="accessibility-close-btn" aria-label="Tutup panel aksesibilitas">
                ✕
            </button>
        </div>

        <!-- Panel Content -->
        <div class="accessibility-panel-content">
            <!-- Font Size Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>📝</span>
                    <span>Ukuran Teks</span>
                </h4>
                <div class="accessibility-controls">
                    <button onclick="setFontSize('small')" class="font-size-btn" id="fontSmallBtn">A-</button>
                    <button onclick="setFontSize('normal')" class="font-size-btn active" id="fontNormalBtn">A</button>
                    <button onclick="setFontSize('large')" class="font-size-btn" id="fontLargeBtn">A+</button>
                    <button onclick="setFontSize('extra-large')" class="font-size-btn" id="fontXLargeBtn">A++</button>
                </div>
            </div>

            <!-- Visual Adjustments Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>🎨</span>
                    <span>Penyesuaian Visual</span>
                </h4>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>👁️</span>
                        <span>Kontras Tinggi</span>
                    </div>
                    <div class="accessibility-switch" id="highContrastControl" onclick="toggleHighContrast()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🔗</span>
                        <span>Garis Bawah Link</span>
                    </div>
                    <div class="accessibility-switch" id="underlineControl" onclick="toggleUnderline()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>📏</span>
                        <span>Jarak Baris</span>
                    </div>
                    <div class="accessibility-switch" id="lineHeightControl" onclick="toggleLineHeight()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>↔️</span>
                        <span>Jarak Huruf</span>
                    </div>
                    <div class="accessibility-switch" id="letterSpacingControl" onclick="toggleLetterSpacing()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🖱️</span>
                        <span>Kursor Besar</span>
                    </div>
                    <div class="accessibility-switch" id="cursorControl" onclick="toggleLargeCursor()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>
            </div>

            <!-- Advanced Features Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>⚙️</span>
                    <span>Fitur Lanjutan</span>
                </h4>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🎬</span>
                        <span>Nonaktifkan Animasi</span>
                    </div>
                    <div class="accessibility-switch" id="animationControl" onclick="toggleAnimations()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>⌨️</span>
                        <span>Navigasi Keyboard Ditingkatkan</span>
                    </div>
                    <div class="accessibility-switch" id="keyboardControl" onclick="toggleKeyboardNavigation()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>
            </div>

            <!-- Reset Section -->
            <div class="accessibility-section">
                <button onclick="resetAllAccessibility()" class="btn btn-danger w-100" aria-label="Reset semua pengaturan aksesibilitas">
                    <i class="fas fa-sync-alt me-2"></i>
                    Reset Semua Pengaturan
                </button>
            </div>
        </div>
    </div>
    @include('layouts.navigation')

    <!-- Page Content -->
    <main id="main-content" class="py-4">
        {{ $slot }}
    </main>

    <!-- Footer for consistency -->
    <footer class="footer bg-light mt-auto py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}</h5>
                    <p>
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span>
                    </p>
                    <p>
                        <i class="fas fa-phone me-2"></i>
                        <span>(0341) 711500</span>
                    </p>
                    <p>
                        <i class="fas fa-envelope me-2"></i>
                        <span>mtsnmalang2adm@gmail.com</span>
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>{{ __("Kontak") }}</h5>
                    <p>{{ __("Jam Operasional") }}:</p>
                    <ul class="list-unstyled">
                        <li>{{ __("Senin - Jumat: 07:30 - 15:00") }}</li>
                        <li>{{ __("Sabtu: 07:30 - 12:00") }}</li>
                        <li>{{ __("Minggu & Libur: Tutup") }}</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}. {{ __("Hak Cipta Dilindungi.") }}</p>
            </div>
        </div>
    </footer>

    {{-- Bootstrap JS and AOS now loaded via Vite (bootstrap-bundle.js) --}}

    <!-- Custom JavaScript -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
        });

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
    @stack('scripts')
</body>
</html>