<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $themePublic ?? 'light' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Vite Assets: Tailwind CSS and Font Awesome (Local - No CDN) --}}
        @if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning())
            @vite(['resources/css/bootstrap-custom.css', 'resources/css/app.css', 'resources/css/dark-mode.css', 'resources/css/public-layout.css', 'resources/css/accessibility.css', 'resources/css/loading.css', 'resources/js/bootstrap-bundle.js', 'resources/js/app.js', 'resources/js/accessibility.js'])
        @else
            {!! App\Helpers\AssetHelper::css('resources/css/bootstrap-custom.css') !!}
            {!! App\Helpers\AssetHelper::css('resources/css/app.css') !!}
            {!! App\Helpers\AssetHelper::css('resources/css/dark-mode.css') !!}
            {!! App\Helpers\AssetHelper::css('resources/css/public-layout.css') !!}
            {!! App\Helpers\AssetHelper::css('resources/css/accessibility.css') !!}
            {!! App\Helpers\AssetHelper::css('resources/css/loading.css') !!}
            {!! App\Helpers\AssetHelper::js('resources/js/bootstrap-bundle.js', false) !!}
            {!! App\Helpers\AssetHelper::js('resources/js/app.js', false) !!}
            {!! App\Helpers\AssetHelper::js('resources/js/accessibility.js', false) !!}
        @endif
    </head>
    <body class="tw-font-sans tw-antialiased">
    <!-- Page Loading Overlay -->
    <div id="page-loading-overlay">
        <div class="loading-content">
            <!-- Modern Spinner -->
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>

            <!-- Loading Text -->
            <div class="loading-text">PTSP MTsN 2 Kota Malang</div>
            <div class="loading-subtext">Memuat Halaman...</div>

            <!-- Loading Dots -->
            <div class="loading-dots">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>

            <!-- Progress Bar -->
            <div class="loading-progress">
                <div class="loading-progress-bar"></div>
            </div>
        </div>
    </div>

    <script>
        // Hide loading overlay when page is fully loaded
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('page-loading-overlay');
            if (loadingOverlay) {
                // Add a small delay for smoother transition
                setTimeout(function() {
                    loadingOverlay.classList.add('hidden');
                }, 300);
            }
        });

        // Fallback: Hide loading after maximum 5 seconds even if load event hasn't fired
        setTimeout(function() {
            const loadingOverlay = document.getElementById('page-loading-overlay');
            if (loadingOverlay && !loadingOverlay.classList.contains('hidden')) {
                loadingOverlay.classList.add('hidden');
            }
        }, 5000);
    </script>

    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <!-- Floating Action Buttons - Left Side -->
    <div class="floating-action-buttons-left">
        <!-- Accessibility Button -->
        <button id="accessibility-btn"
                class="floating-btn accessibility-btn pulse">
            <img src="{{ asset('images/Accessibility.png') }}" alt="Accessibility" class="w-full h-full object-contain">
        </button>

        <!-- Back to Top Button -->
        <button onclick="scrollToTop()"
                id="back-to-top-btn"
                class="floating-btn back-to-top-btn">
            <i class="fas fa-arrow-up"></i>
        </button>

        <!-- WhatsApp Button with Dropdown -->
        <div class="whatsapp-dropdown">
            <button type="button" class="floating-btn whatsapp-btn" id="whatsapp-toggle">
                <img src="{{ asset('images/WhatsApp.webp') }}" alt="WhatsApp" class="w-full h-full object-contain">
            </button>
            <div class="whatsapp-dropdown-menu" id="whatsapp-dropdown-menu">
                <div class="whatsapp-option">
                    <a href="https://wa.me/6285183367500?text=Halo, saya ingin bertanya tentang layanan PTSP MTsN 2 KOTA MALANG"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-dropdown-link">
                        <img src="{{ asset('images/WhatsApp.webp') }}" alt="WhatsApp" class="whatsapp-icon"> PTSP (085183367500)
                    </a>
                </div>
                <div class="whatsapp-option">
                    <a href="https://wa.me/6285156631610?text=Halo, saya ingin bertanya tentang layanan Komite MTsN 2 KOTA MALANG"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-dropdown-link">
                        <img src="{{ asset('images/WhatsApp.webp') }}" alt="WhatsApp" class="whatsapp-icon"> Komite (085156631610)
                    </a>
                </div>
                <div class="whatsapp-option">
                    <a href="https://wa.me/6285183375008?text=Halo, saya ingin menyampaikan pengaduan ke MTsN 2 KOTA MALANG"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-dropdown-link">
                        <img src="{{ asset('images/WhatsApp.webp') }}" alt="WhatsApp" class="whatsapp-icon"> Pengaduan (085183375008)
                    </a>
                </div>
            </div>
        </div>
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
        <!-- Dashboard Layout with Fixed Sidebar and Topbar -->
        @auth
            <div class="dashboard-layout">
                <!-- Fixed Sidebar -->
                @include('layouts.sidebar')

                <div class="main-panel">
                    <!-- Fixed Topbar -->
                    <div class="topbar">
                        <!-- Page Heading (Topbar content) -->
                        @hasSection('header')
                            <header class="topbar-content" role="banner">
                                <div class="container-fluid px-4">
                                    @yield('header')
                                </div>
                            </header>
                        @endif
                    </div>

                    <!-- Main Content Wrapper -->
                    <main class="content-wrapper">
                        @yield('content')
                    </main>
                    
                    <!-- Footer -->
                    <footer class="footer" role="contentinfo">
                        <div class="container-fluid px-4">
                            <div class="text-center py-3 text-muted">
                                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                            </div>
                        </div>
                    </footer>
                </div>
            </div>

            <style>
                <!-- MARKER: layouts.app -->
                /* 1. Atur pembungkus utama untuk menggunakan Flexbox */
                .dashboard-layout {
                    display: flex; /* Ini kuncinya! */
                    width: 100%;
                    min-height: 100vh; /* Setidaknya setinggi layar */
                }

                /* 2. Atur lebar Sidebar */
                @media (min-width: 992px) {
                    .sidebar {
                        width: 240px; /* Atur lebar sidebar sesuai kebutuhan */
                        transition: width 0.3s ease;
                    }

                    .sidebar.collapsed {
                        width: 80px;
                    }

                    .main-panel {
                        margin-left: 240px;
                        transition: margin-left 0.3s ease;
                    }

                    .main-panel.collapsed {
                        margin-left: 80px;
                    }
                }
                
                @media (min-width: 1600px) {
                    .sidebar {
                        width: 260px;
                    }
                    .main-panel {
                        margin-left: 260px;
                    }
                    .sidebar.collapsed {
                        width: 80px;
                    }
                    .main-panel.collapsed {
                        margin-left: 80px;
                    }
                }


                /* 3. Atur panel utama (Topbar + Konten) */
                .main-panel {
                    /* Gunakan sisa ruang yang tersedia */
                    flex-grow: 1; 
                    
                    /* Atur agar Topbar dan Konten tersusun vertikal */
                    display: flex;
                    flex-direction: column;
                }

                /* 4. Styling Topbar dan Konten */
                .topbar {
                    height: 60px; /* Tinggi Topbar */
                    background-color: #fff;
                    border-bottom: 1px solid #eee;
                }

                .content-wrapper {
                    /* Gunakan sisa ruang vertikal di main-panel */
                    flex-grow: 1; 
                    padding: 20px;
                    background-color: #f4f7f6;
                    overflow-y: auto; /* Biarkan konten bisa di-scroll jika panjang */
                }

                .footer {
                    padding: 15px 20px;
                    background-color: #fff;
                    border-top: 1px solid #eee;
                }
            </style>
        @else
            <!-- Public Layout without Sidebar -->
            <div class="tw-min-h-screen tw-bg-gray-100 dark:tw-bg-gray-900">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @hasSection('header')
                    <header class="tw-bg-white dark:tw-bg-gray-800 tw-shadow">
                        <div class="tw-max-w-7xl tw-mx-auto tw-py-6 tw-px-4 sm:tw-px-6 lg:tw-px-8">
                            @yield('header')
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main id="main-content">
                    @yield('content')
                </main>
            </div>
        @endauth
        

    </body>
</html>
