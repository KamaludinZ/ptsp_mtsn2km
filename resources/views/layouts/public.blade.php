<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'PTSP MTsN 2 Kota Malang'))</title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#14532d">

    {{-- Vite Assets: Bootstrap, Font Awesome, AOS, Tailwind (Local - No CDN) --}}
    @vite([
        'resources/css/bootstrap-custom.css',
        'resources/css/app.css',
        'resources/css/dark-mode.css',
        'resources/css/public-layout.css',
        'resources/css/accessibility.css',
        'resources/js/bootstrap-bundle.js',
        'resources/js/app.js',
        'resources/js/accessibility.js'
    ])

    @stack('styles')
</head>
<body class="font-normal">
    <!-- Contact Header -->
    <div style="background-color: #111827; color: white; padding: 0.5rem 0; font-size: 0.75rem;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 0.5rem;">
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 0.5rem; padding: 0; white-space: nowrap; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;">
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-map-marker-alt" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-phone" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>(0341) 711500</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-envelope" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>mtsnmalang2adm@gmail.com</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-globe" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>www.mtsn2kotamalang.sch.id</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fab fa-whatsapp" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>0851 8336 7500 (PTSP)</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fab fa-whatsapp" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>0851 8337 5008 (Pengaduan)</span>
                </div>
            </div>
        </div>
    </div>

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
    </div>

    <!-- Floating Action Button - Right Side -->
    <div class="floating-action-buttons-right">
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

    <!-- Main Content -->
    <main id="main-content" role="main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="footer-brand">
                        @if(config('app.logo'))
                            <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }} Logo"
                                 style="width: 48px; height: 48px; object-fit: contain; border-radius: 12px;"
                                 class="footer-logo">
                        @else
                            <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-building text-white"></i>
                            </div>
                        @endif
                        <div>
                            <h3>{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}</h3>
                            <p class="text-muted small mb-0">Pelayanan Terpadu Satu Pintu</p>
                        </div>
                    </div>
                    <p class="mb-3">
                        Pelayanan Terpadu Satu Pintu sesuai Permen PANRB 15/2014 untuk kemudahan akses layanan masyarakat.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-5" aria-label="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-white fs-5" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-white fs-5" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white fs-5" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 mb-4">
                    <h5 class="text-white mb-3">Kontak Kami</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>(0341) 711500</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>mtsnmalang2adm@gmail.com</span>
                        </li>
                        <li>
                            <i class="fas fa-globe"></i>
                            <span>www.mtsn2kotamalang.sch.id</span>
                        </li>
                        <li>
                            <i class="fab fa-whatsapp"></i>
                            <span>0851 8336 7500 (PTSP)</span>
                        </li>
                        <li>
                            <i class="fab fa-whatsapp"></i>
                            <span>0851 8337 5008 (Pengaduan)</span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 mb-4">
                    <h5 class="text-white mb-3">Jam Operasional</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Senin - Kamis</strong><br>
                                <span class="text-white">07.00 - 15.00 WIB</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Jumat</strong><br>
                                <span class="text-white">07.00 - 11.00 WIB</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-calendar-times"></i>
                            <div>
                                <strong>Sabtu - Minggu</strong><br>
                                <span class="text-white">Tutup</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 mb-4">
                    <h5 class="text-white mb-3">Link Terkait</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://kemenag.go.id" target="_blank" rel="noopener noreferrer">
                                Kementerian Agama RI
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://kanwil.kemenag.go.id/jatim" target="_blank" rel="noopener noreferrer">
                                Kanwil Kemenag Jatim
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://kankemenag.malangkota.go.id" target="_blank" rel="noopener noreferrer">
                                Kemenag Kota Malang
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://lapor.go.id" target="_blank" rel="noopener noreferrer">
                                SP4N Lapor
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://ppid.kemenag.go.id" target="_blank" rel="noopener noreferrer">
                                PPID Kemenag
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4" style="border-color: var(--bs-gray-700);">

            <div class="row align-items-center">
                <div class="col-md-7">
                    <p class="mb-0">
                        <i class="fas fa-code-branch text-info me-2" style="font-size: 0.8rem;"></i>
                        <span class="me-3" style="font-size: 0.8rem; color: var(--bs-gray-400);">v1.0.0</span>
                        &copy; {{ date('Y') }} {{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}. Hak Cipta Dilindungi.
                    </p>
                </div>
                <div class="col-md-5 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-code me-1" style="font-size: 0.8rem;"></i> Dikembangkan dengan <i class="fas fa-heart text-danger mx-1" style="font-size: 0.8rem;"></i> oleh Tim PUSKOM
                    </p>
                </div>
            </div>
        </div>
    </footer>

    {{-- Scripts now loaded via Vite (bootstrap-bundle.js includes Bootstrap JS and AOS) --}}
    <script>
        // Initialize AOS - Optimized for performance
        if (typeof AOS !== 'undefined') {
            // Disable AOS on mobile devices for better performance
            const isMobile = window.innerWidth < 768;
            if (!isMobile) {
                AOS.init({
                    duration: 400, // Reduced from 800ms
                    once: true,
                    disable: 'mobile', // Disable on mobile
                    offset: 50, // Reduced offset for faster trigger
                    delay: 0, // No delay
                    easing: 'ease-out', // Simpler easing
                    mirror: false, // No animation when scrolling back
                    anchorPlacement: 'top-bottom',
                    startEvent: 'DOMContentLoaded',
                    animatedClassName: 'aos-animate',
                    initClassName: 'aos-init',
                });
            }
        }

        // Accessibility Panel Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const accessibilityPanel = document.getElementById('accessibilityPanel');
            const accessibilityBtn = document.getElementById('accessibility-btn');

            if (accessibilityBtn) {
                accessibilityBtn.addEventListener('click', function() {
                    toggleAccessibilitySidebar();
                });
            }

            // Close panel when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInsidePanel = accessibilityPanel.contains(event.target);
                const isClickOnButton = accessibilityBtn.contains(event.target);

                if (!isClickInsidePanel && !isClickOnButton && accessibilityPanel.classList.contains('open')) {
                    toggleAccessibilitySidebar();
                }
            });

            // Load saved accessibility preferences
            loadAccessibilityPreferences();
        });

        function toggleAccessibilitySidebar() {
            const panel = document.getElementById('accessibilityPanel');
            const btn = document.getElementById('accessibility-btn');

            if (panel.classList.contains('open')) {
                panel.classList.remove('open');
                btn.innerHTML = '<span style="line-height: 1;">♿</span>';
                btn.classList.add('pulse');
            } else {
                panel.classList.add('open');
                btn.innerHTML = '<i class="fas fa-times" style="line-height: 1;"></i>';
                btn.classList.remove('pulse');
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide back to top button
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.getElementById('back-to-top-btn');
            if (window.pageYOffset > 300) {
                backToTopBtn.style.opacity = '1';
                backToTopBtn.style.visibility = 'visible';
            } else {
                backToTopBtn.style.opacity = '0';
                backToTopBtn.style.visibility = 'hidden';
            }
        });

        // Font Size Functions
        function setFontSize(size) {
            const body = document.body;
            body.className = body.className.replace(/font-\w+/g, '');
            body.classList.add('font-' + size);

            // Update active button
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('font' + size.charAt(0).toUpperCase() + size.slice(1).replace('-', '') + 'Btn').classList.add('active');

            localStorage.setItem('fontSize', size);
        }

        // Toggle Functions
        function toggleHighContrast() {
            document.body.classList.toggle('high-contrast');
            toggleSwitch('highContrastControl');
            localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
        }

        function toggleUnderline() {
            document.body.classList.toggle('underline-links');
            toggleSwitch('underlineControl');
            localStorage.setItem('underlineLinks', document.body.classList.contains('underline-links'));
        }

        function toggleLineHeight() {
            document.body.classList.toggle('increased-line-height');
            toggleSwitch('lineHeightControl');
            localStorage.setItem('lineHeight', document.body.classList.contains('increased-line-height'));
        }

        function toggleLetterSpacing() {
            document.body.classList.toggle('increased-letter-spacing');
            toggleSwitch('letterSpacingControl');
            localStorage.setItem('letterSpacing', document.body.classList.contains('increased-letter-spacing'));
        }

        function toggleLargeCursor() {
            document.body.classList.toggle('large-cursor');
            toggleSwitch('cursorControl');
            localStorage.setItem('largeCursor', document.body.classList.contains('large-cursor'));
        }

        function toggleAnimations() {
            document.body.classList.toggle('no-animations');
            toggleSwitch('animationControl');
            localStorage.setItem('noAnimations', document.body.classList.contains('no-animations'));
        }

        function toggleKeyboardNavigation() {
            document.body.classList.toggle('keyboard-navigation');
            toggleSwitch('keyboardControl');
            localStorage.setItem('keyboardNavigation', document.body.classList.contains('keyboard-navigation'));
        }

        function toggleSwitch(id) {
            const switchEl = document.getElementById(id);
            switchEl.classList.toggle('active');
            const isActive = switchEl.classList.contains('active');
            switchEl.setAttribute('aria-checked', isActive);
        }

        function resetAllAccessibility() {
            // Remove all accessibility classes
            document.body.className = 'font-normal';

            // Reset all switches
            document.querySelectorAll('.accessibility-switch').forEach(sw => {
                sw.classList.remove('active');
                sw.setAttribute('aria-checked', 'false');
            });

            // Reset font size buttons
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('fontNormalBtn').classList.add('active');

            // Clear localStorage
            localStorage.removeItem('fontSize');
            localStorage.removeItem('highContrast');
            localStorage.removeItem('underlineLinks');
            localStorage.removeItem('lineHeight');
            localStorage.removeItem('letterSpacing');
            localStorage.removeItem('largeCursor');
            localStorage.removeItem('noAnimations');
            localStorage.removeItem('keyboardNavigation');
        }

        function loadAccessibilityPreferences() {
            // Load font size
            const fontSize = localStorage.getItem('fontSize');
            if (fontSize) {
                setFontSize(fontSize);
            }

            // Load toggles
            if (localStorage.getItem('highContrast') === 'true') {
                document.body.classList.add('high-contrast');
                document.getElementById('highContrastControl').classList.add('active');
            }
            if (localStorage.getItem('underlineLinks') === 'true') {
                document.body.classList.add('underline-links');
                document.getElementById('underlineControl').classList.add('active');
            }
            if (localStorage.getItem('lineHeight') === 'true') {
                document.body.classList.add('increased-line-height');
                document.getElementById('lineHeightControl').classList.add('active');
            }
            if (localStorage.getItem('letterSpacing') === 'true') {
                document.body.classList.add('increased-letter-spacing');
                document.getElementById('letterSpacingControl').classList.add('active');
            }
            if (localStorage.getItem('largeCursor') === 'true') {
                document.body.classList.add('large-cursor');
                document.getElementById('cursorControl').classList.add('active');
            }
            if (localStorage.getItem('noAnimations') === 'true') {
                document.body.classList.add('no-animations');
                document.getElementById('animationControl').classList.add('active');
            }
            if (localStorage.getItem('keyboardNavigation') === 'true') {
                document.body.classList.add('keyboard-navigation');
                document.getElementById('keyboardControl').classList.add('active');
            }
        }

        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);

            const icon = document.getElementById('themeIcon');
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

            localStorage.setItem('theme', newTheme);
        }

        // Load theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (savedTheme === 'dark') {
            document.getElementById('themeIcon').className = 'fas fa-sun';
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
