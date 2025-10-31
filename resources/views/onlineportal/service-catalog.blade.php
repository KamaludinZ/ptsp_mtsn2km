<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <meta name="description" content="Katalog Layanan - {{ config('app.name_full', 'MTsN 2 Kota Malang') }} - Daftar lengkap layanan yang tersedia sesuai Permen PANRB 15/2014">
    <meta name="keywords" content="Katalog Layanan, PTSP, {{ config('app.name_full', 'MTsN 2 Kota Malang') }}, Pelayanan, Permen PANRB, Layanan Digital, Satu Pintu">
    <meta name="author" content="{{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Katalog Layanan - {{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">
    <meta property="og:description" content="Daftar lengkap layanan yang tersedia sesuai Permen PANRB 15/2014">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/ptsp-social.jpg') }}">
    <meta property="og:url" content="{{ url('/services') }}">
    <meta property="og:site_name" content="{{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Katalog Layanan - {{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">
    <meta name="twitter:description" content="Daftar lengkap layanan yang tersedia sesuai Permen PANRB 15/2014">
    <meta name="twitter:image" content="{{ asset('images/ptsp-social.jpg') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/services') }}">

    <title>Katalog Layanan - {{ config('app.name', 'PTSP MTsN 2 Kota Malang') }}</title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#14532d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PTSP MTsN 2">
    <meta name="msapplication-TileColor" content="#14532d">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preload" href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" as="style">
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Dark Mode CSS -->
    <link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        /* CSS Variables for Theme Support */
        :root {
            /* Light theme colors */
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

            /* Semantic colors */
            --bs-success: #10b981;
            --bs-warning: #f59e0b;
            --bs-danger: #ef4444;
            --bs-info: #3b82f6;

            /* Accessibility colors */
            --bs-focus: #2563eb;
            --bs-text: #1f2937;
            --bs-bg: #ffffff;
            --bs-surface: #f9fafb;
            --bs-border: #e5e7eb;

            /* Typography */
            --bs-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --bs-font-serif: Georgia, 'Times New Roman', serif;
            --bs-font-mono: 'Fira Code', 'Monaco', 'Consolas', monospace;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-light) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--bs-secondary) 0%, var(--bs-secondary-light) 100%);
            --gradient-dark: linear-gradient(135deg, var(--bs-gray-900) 0%, var(--bs-gray-700) 100%);

            /* Animations */
            --transition-fast: 0.15s ease-in-out;
            --transition-normal: 0.3s ease-in-out;
            --transition-slow: 0.5s ease-in-out;

            /* Border radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;

            /* Z-index */
            --z-dropdown: 1000;
            --z-sticky: 1020;
            --z-fixed: 1030;
            --z-modal: 1050;
            --z-popover: 1070;
            --z-tooltip: 1080;
            --z-toast: 1090;
        }

        /* Enhanced Bootstrap Overrides */
        .navbar {
            background: var(--bs-white) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--bs-border);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            padding: 0 0;
        }
        .navbar .container-fluid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 100%;
        }
        [data-theme="dark"] .navbar {
            background: var(--bs-surface) !important;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--bs-primary) !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-right: auto;
            flex-shrink: 0;
        }
        .navbar-brand:hover {
            transform: translateY(-2px);
        }
        .brand-text {
            font-size: clamp(1rem, 2.5vw, 1.5rem);
            font-weight: 700;
            line-height: 1.2;
            display: inline-block;
        }
        @media (max-width: 768px) {
            .brand-text {
                font-size: 0.9rem;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }
        .navbar-collapse {
            flex-grow: 0;
        }
        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--bs-gray-700) !important;
            margin: 0 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }
        [data-theme="dark"] .navbar-nav .nav-link {
            color: var(--bs-gray-300) !important;
        }
        .navbar-nav .nav-link:hover {
            background: var(--bs-primary);
            color: var(--bs-white) !important;
            transform: translateY(-1px);
        }

        /* Hero Section */
        .hero-section {
            min-height: 50vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-light) 100%);
        }

        /* Service Cards */
        .service-card {
            background: var(--bs-white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            height: 100%;
            transition: var(--transition-normal);
            border: 1px solid var(--bs-border);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }
        [data-theme="dark"] .service-card {
            background: var(--bs-gray-800);
            border-color: var(--bs-gray-700);
        }
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--bs-primary);
        }
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition-normal);
        }
        .service-card:hover::before {
            transform: scaleX(1);
        }

        /* Footer Styles */
        .footer {
            background: var(--bs-gray-900);
            color: var(--bs-white);
            padding: 4rem 0 2rem;
            margin-top: 5rem;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-links li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: var(--bs-gray-300);
        }
        .footer-links i {
            width: 20px;
            text-align: center;
            margin-top: 2px;
            color: var(--bs-primary);
        }

        /* Search and Filter */
        .search-section {
            background: var(--bs-white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--bs-border);
        }
        [data-theme="dark"] .search-section {
            background: var(--bs-gray-800);
            border-color: var(--bs-gray-700);
        }

        /* Standards Compliance */
        .standards-section {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: var(--radius-lg);
            padding: 3rem;
            text-align: center;
            margin-top: 3rem;
        }
        [data-theme="dark"] .standards-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        }

        /* Focus styles for accessibility */
        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--bs-focus);
            outline-offset: 2px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .brand-text {
                font-size: 0.8rem;
                max-width: 120px;
            }
            .navbar-brand div,
            .navbar-brand .brand-logo {
                width: 32px !important;
                height: 32px !important;
            }
            .footer-logo {
                width: 40px !important;
                height: 40px !important;
            }
        }

        /* Print styles */
        @media print {
            .theme-toggle,
            .navbar,
            footer {
                display: none;
            }
            body {
                font-size: 12pt;
                color: black;
                background: white;
            }
            .service-card {
                break-inside: avoid;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link position-absolute top-0 start-0 z-50 btn btn-primary ms-3 mt-3" style="transform: translateY(-100px);">Skip to main content</a>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top" role="navigation" aria-label="Navigasi utama">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('home') }}" aria-label="{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }} - Beranda">
                @if(config('app.logo'))
                    <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }} Logo"
                         style="width: 40px; height: 40px; object-fit: contain; border-radius: 12px;"
                         class="brand-logo">
                @else
                    <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-building text-white"></i>
                    </div>
                @endif
                <span class="brand-text">{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
                    style="border: none; color: var(--bs-primary);">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/services">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.visitor.book') }}">Buku Tamu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('supervision.skm.survey') }}">Survei</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('supervision.complaints.dashboard') }}">Pengaduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('onlineportal.track.ticket.form') }}">Lacak Tiket</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('onlineportal.dashboard') }}">
                                <i class="fas fa-user-circle me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link text-danger">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" data-aos="fade">
        <div class="container">
            <div class="row align-items-center min-vh-100 py-5">
                <div class="col-lg-12 text-center text-white">
                    <h1 class="display-3 fw-bold mb-4" data-aos="fade-up">
                        <i class="fas fa-concierge-bell me-3"></i>
                        Katalog Layanan
                    </h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Daftar lengkap layanan yang tersedia sesuai Permen PANRB 15/2014 tentang Standar Pelayanan Publik
                    </p>
                    <div data-aos="fade-up" data-aos-delay="200">
                        <span class="badge bg-white text-primary fs-6 p-3">
                            <i class="fas fa-file-contract me-2"></i>
                            15+ Jenis Layanan Tersedia
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main id="main-content" role="main" class="container py-5">
        <!-- Search and Filter Section -->
        <section class="search-section" data-aos="fade-up">
            <div class="row">
                <div class="col-lg-8">
                    <div class="position-relative">
                        <div class="position-absolute start-0 top-50 translate-middle-y ps-3">
                            <i class="fas fa-search text-muted"></i>
                        </div>
                        <input type="text"
                               id="searchInput"
                               class="form-control form-control-lg ps-5"
                               placeholder="Cari layanan yang Anda butuhkan..."
                               style="border-radius: var(--radius-lg);">
                    </div>
                </div>
                <div class="col-lg-4">
                    <select id="categoryFilter" class="form-select form-select-lg" style="border-radius: var(--radius-lg);">
                        <option value="">Semua Kategori</option>
                        <option value="akademik">Akademik</option>
                        <option value="administratif">Administratif</option>
                        <option value="kesiswaan">Kesiswaan</option>
                        <option value="sarana">Sarana & Prasarana</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Services Grid -->
        <section class="services-section">
            <div class="row g-4" id="servicesGrid">
                @foreach($services as $service)
                <div class="col-lg-4 col-md-6 service-card-wrapper"
                     data-name="{{ strtolower($service->name) }}"
                     data-category="{{ strtolower($service->category) }}"
                     data-aos="fade-up">
                    <div class="service-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary mb-2">{{ $service->code }}</span>
                                <h5 class="card-title fw-bold">{{ $service->name }}</h5>
                            </div>
                            <div>
                                @if($service->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>Non-Aktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-muted mb-4">{{ $service->description }}</p>

                        <div class="mb-4">
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-purple-100 text-purple-800">
                                    <i class="fas fa-user me-1"></i>{{ $service->target_group }}
                                </span>
                                <span class="badge bg-warning-100 text-warning-800">
                                    <i class="fas fa-clock me-1"></i>{{ $service->processing_time }}
                                </span>
                                <span class="badge bg-danger-100 text-danger-800">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    {{ $service->fee ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis' }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <button class="btn btn-outline-primary btn-sm" onclick="showServiceDetail('{{ $service->slug }}')">
                                <i class="fas fa-info-circle me-1"></i>Detail
                            </button>

                            @auth
                                <a href="{{ route('onlineportal.service.apply', $service->slug) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-file-medical me-1"></i>Ajukan
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Standards Compliance Section -->
        <section class="standards-section" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center">
                        <i class="fas fa-info-circle text-primary fs-1 mb-3"></i>
                        <h3 class="fw-bold mb-3">Kepatuhan Permen PANRB 15/2014</h3>
                        <p class="lead mb-4">
                            Semua layanan ini telah diselarangkan dengan 14 komponen standar pelayanan sesuai Peraturan Menteri PANRB Nomor 15 Tahun 2014 tentang Pedoman Pelayanan Publik.
                        </p>
                        <button class="btn btn-primary btn-lg" onclick="showStandardsModal()">
                            <i class="fas fa-file-contract me-2"></i>
                            Lihat Standar Pelayanan Lengkap
                        </button>
                    </div>
                </div>
            </div>
        </section>
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
                    <h5 class="text-white mb-3">Link Terkait</h5>
                    <ul class="footer-links">
                        <li><a href="/" class="text-white-50 text-decoration-none">Beranda</a></li>
                        <li><a href="/services" class="text-white-50 text-decoration-none">Katalog Layanan</a></li>
                        <li><a href="{{ route('public.visitor.book') }}" class="text-white-50 text-decoration-none">Buku Tamu</a></li>
                        <li><a href="{{ route('supervision.complaints.dashboard') }}" class="text-white-50 text-decoration-none">Pengaduan</a></li>
                        <li><a href="{{ route('supervision.skm.survey') }}" class="text-white-50 text-decoration-none">Survei Kepuasan</a></li>
                        <li><a href="{{ route('onlineportal.track.ticket.form') }}" class="text-white-50 text-decoration-none">Lacak Tiket</a></li>
                        @auth
                            <li><a href="{{ route('onlineportal.dashboard') }}" class="text-white-50 text-decoration-none">Dashboard</a></li>
                        @endauth
                    </ul>
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
            </div>

            <div class="border-top border-secondary mt-4 pt-4 text-center">
                <p class="mb-0 text-white-50">
                    &copy; {{ date('Y') }} {{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}. Hak Cipta Dilindungi.
                </p>
                <p class="small text-white-50 mb-0">
                    Dikembangkan dengan <i class="fas fa-heart text-danger"></i> untuk kemudahan pelayanan masyarakat
                </p>
            </div>
        </div>
    </footer>

    <!-- Service Detail Modal -->
    <div class="modal fade" id="serviceDetailModal" tabindex="-1" aria-labelledby="serviceDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceDetailModalLabel">Detail Layanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalServiceContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    @auth
                        <button type="button" class="btn btn-primary" id="modalApplyButton">Ajukan Layanan</button>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Standards Modal -->
    <div class="modal fade" id="standardsModal" tabindex="-1" aria-labelledby="standardsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="standardsModalLabel">
                        <i class="fas fa-list-check me-2"></i>
                        14 Komponen Standar Pelayanan Publik
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">1. Kebijakan Pelayanan</h6>
                                    <p class="card-text small">Kebijakan yang diimplementasikan untuk memastikan pelayanan yang berkualitas.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">2. Maklumat Pelayanan</h6>
                                    <p class="card-text small">Informasi lengkap tentang layanan yang disediakan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">3. Standar Pelayanan</h6>
                                    <p class="card-text small">Kriteria yang digunakan untuk mengukur kualitas pelayanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">4. Prosedur Pelayanan</h6>
                                    <p class="card-text small">Tahapan yang harus dilalui dalam mendapatkan pelayanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">5. Waktu Pelayanan</h6>
                                    <p class="card-text small">Durasi yang dibutuhkan untuk menyelesaikan setiap layanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">6. Biaya/Tarif</h6>
                                    <p class="card-text small">Rincian biaya untuk setiap jenis layanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">7. Produk Pelayanan</h6>
                                    <p class="card-text small">Hasil akhir yang diterima oleh pemohon layanan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">8. Sarana & Prasarana</h6>
                                    <p class="card-text small">Fasilitas yang disediakan untuk mendukung pelayanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">9. Kompetensi Pelaksana</h6>
                                    <p class="card-text small">Kualifikasi dan kemampuan petugas pelayanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">10. Pengaduan</h6>
                                    <p class="card-text small">Mekanisme penyampaian keluhan dan saran.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">11. Penilaian Kepuasan</h6>
                                    <p class="card-text small">Sistem untuk mengukur tingkat kepuasan masyarakat.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">12. Jaminan Pelayanan</h6>
                                    <p class="card-text small">Jaminan yang diberikan untuk memastikan kualitas layanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">13. Reward & Punishment</h6>
                                    <p class="card-text small">Sistem penghargaan dan sanksi untuk pelaksana layanan.</p>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">14. Integrasi Teknologi</h6>
                                    <p class="card-text small">Penerapan teknologi informasi untuk meningkatkan kualitas layanan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-3" style="display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Search and Filter Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const serviceCards = document.querySelectorAll('.service-card-wrapper');

            function filterServices() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCategory = categoryFilter.value.toLowerCase();

                serviceCards.forEach(card => {
                    const serviceName = card.dataset.name;
                    const serviceCategory = card.dataset.category;

                    const matchesSearch = serviceName.includes(searchTerm);
                    const matchesCategory = !selectedCategory || serviceCategory === selectedCategory;

                    if (matchesSearch && matchesCategory) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterServices);
            categoryFilter.addEventListener('change', filterServices);

            // Check URL parameters for initial filtering
            const urlParams = new URLSearchParams(window.location.search);
            const search = urlParams.get('search');
            const category = urlParams.get('category');

            if (search) {
                searchInput.value = search;
            }
            if (category) {
                categoryFilter.value = category;
            }

            filterServices();
        });

        // Service Detail Modal
        function showServiceDetail(serviceSlug) {
            const modal = new bootstrap.Modal(document.getElementById('serviceDetailModal'));
            const modalContent = document.getElementById('modalServiceContent');
            const modalApplyButton = document.getElementById('modalApplyButton');

            // Load service detail (static content for now)
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Persyaratan</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Fotokopi kartu pelajar</li>
                            <li><i class="fas fa-check text-success me-2"></i>Surat keterangan dari sekolah</li>
                            <li><i class="fas fa-check text-success me-2"></i>Formulir permohonan yang telah diisi</li>
                            <li><i class="fas fa-check text-success me-2"></i>Pas foto terbaru</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Prosedur</h6>
                        <ol class="list-unstyled">
                            <li class="mb-2"><strong>1.</strong> Mengajukan permohonan secara online atau datang langsung</li>
                            <li class="mb-2"><strong>2.</strong> Verifikasi berkas persyaratan</li>
                            <li class="mb-2"><strong>3.</strong> Proses pembuatan surat</li>
                            <li><strong>4.</strong> Penandatanganan dan pengambilan</li>
                        </ol>
                    </div>
                </div>
                <div class="mt-3">
                    <h6 class="text-primary mb-3">Jaminan Pelayanan</h6>
                    <p>Layanan ini dilaksanakan sesuai standar operasional prosedur (SOP) yang berlaku dan dijamin keabsahannya oleh pihak sekolah.</p>
                </div>
            `;

            if (modalApplyButton) {
                modalApplyButton.onclick = function() {
                    window.location.href = '/services/' + serviceSlug + '/apply';
                };
            }

            modal.show();
        }

        // Standards Modal
        function showStandardsModal() {
            const modal = new bootstrap.Modal(document.getElementById('standardsModal'));
            modal.show();
        }

        // Back to Top Button
        const backToTopButton = document.getElementById('backToTop');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            // ESC key to close modals
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    const modal = bootstrap.Modal.getInstance(openModal);
                    if (modal) {
                        modal.hide();
                    }
                }
            }
        });

        // Skip link functionality
        const skipLink = document.querySelector('.skip-link');
        if (skipLink) {
            skipLink.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector('#main-content');
                if (target) {
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                    target.scrollIntoView();
                }
            });
        }
    </script>
</body>
</html>