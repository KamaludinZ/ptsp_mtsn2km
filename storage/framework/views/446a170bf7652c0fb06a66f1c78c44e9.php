<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo e(config('app.description', 'Pelayanan Terpadu Satu Pintu ' . config('app.name_full', 'MTsN 2 Kota Malang') . ' - Layanan cepat, transparan, dan akuntabel sesuai Permen PANRB 15/2014')); ?>">
    <meta name="keywords" content="PTSP, <?php echo e(config('app.name_full', 'MTsN 2 Kota Malang')); ?>, Pelayanan, Permen PANRB, Layanan Digital, Satu Pintu">
    <meta name="author" content="<?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?> - Pelayanan Terpadu Satu Pintu">
    <meta property="og:description" content="<?php echo e(config('app.description', 'Layanan cepat, transparan, dan akuntabel sesuai Permen PANRB 15/2014')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo e(asset('images/ptsp-social.jpg')); ?>">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:site_name" content="<?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?>">
    <meta name="twitter:description" content="<?php echo e(config('app.description', 'Pelayanan Terpadu Satu Pintu - Cepat, Transparan, Akuntabel')); ?>">
    <meta name="twitter:image" content="<?php echo e(asset('images/ptsp-social.jpg')); ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo e(url('/')); ?>">

    <title><?php echo e(config('app.name', 'PTSP MTsN 2 Kota Malang')); ?></title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#14532d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PTSP MTsN 2">
    <meta name="msapplication-TileColor" content="#14532d">
    <meta name="msapplication-config" content="<?php echo e(asset('browserconfig.xml')); ?>">

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
    <link href="<?php echo e(asset('css/dark-mode.css')); ?>" rel="stylesheet">

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

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--bs-secondary) 0%, var(--bs-secondary-dark) 100%);
            --gradient-hero: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 50%, var(--bs-secondary) 100%);
        }

        /* Rest of the CSS from the original file would go here, but it's very long */
        /* For brevity, I'm including just the essential parts */
        
        body {
            font-family: var(--bs-font-sans);
            background-color: var(--bs-bg);
            color: var(--bs-text);
            transition: background-color 0.3s ease, color 0.3s ease;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        #hero-section {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            line-height: 1.1;
        }
        
        .hero-subtitle {
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 300;
            margin-bottom: 32px;
            opacity: 0;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.3s forwards;
            line-height: 1.3;
        }
        
        .hero-description {
            font-size: clamp(1rem, 2vw, 1.25rem);
            margin-bottom: 48px;
            opacity: 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.5s forwards;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 48px;
            opacity: 0;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.7s forwards;
        }
        
        .hero-btn-primary, .hero-btn-secondary {
            padding: 16px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transform: translateY(0);
        }
        
        .hero-btn-primary {
            background: rgba(255,255,255,0.2);
            color: white;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .hero-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .hero-btn-primary:hover, .hero-btn-secondary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        
        .hero-info {
            display: flex;
            justify-content: center;
            gap: 48px;
            margin-top: 72px;
            opacity: 0;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.9s forwards;
            flex-wrap: wrap;
        }
        
        .info-item {
            text-align: center;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.98);
                filter: blur(2px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0, 0, 0);
            }
            40%, 43% {
                transform: translate3d(0, -15px, 0);
            }
            70% {
                transform: translate3d(0, -7px, 0);
            }
            90% {
                transform: translate3d(0, -2px, 0);
            }
        }
        
        /* Navigation */
        .navbar {
            background: var(--bs-white) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--bs-border);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            padding: 0 0;
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
        
        .nav-link {
            font-weight: 500;
            color: var(--bs-gray-700) !important;
            margin: 0 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }
        
        [data-theme="dark"] .nav-link {
            color: var(--bs-gray-300) !important;
        }
        
        .nav-link:hover {
            background: var(--bs-primary);
            color: var(--bs-white) !important;
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            color: var(--bs-primary) !important;
            font-weight: 600;
        }
        
        [data-theme="dark"] .nav-link.active {
            color: var(--bs-primary-light) !important;
        }
        
        /* Sections */
        section {
            padding: 4rem 0;
        }
        
        .feature-card, .service-card, .stat-card {
            background: var(--bs-white);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--bs-border);
            height: 100%;
        }
        
        [data-theme="dark"] .feature-card, 
        [data-theme="dark"] .service-card, 
        [data-theme="dark"] .stat-card {
            background: var(--bs-surface);
            border-color: var(--bs-border);
        }
        
        .feature-card:hover, .service-card:hover, .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            background: var(--gradient-primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: var(--bs-white);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        /* Footer */
        .footer {
            background: var(--bs-gray-900);
            color: var(--bs-gray-300);
            padding: 4rem 0 2rem;
            margin-top: 6rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-info {
                flex-direction: column;
                gap: 24px;
            }
            
            .navbar-nav {
                text-align: center;
            }
        }
    </style>
</head>
<body class="font-normal">
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link sr-only">Lewati ke konten utama</a>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top" role="navigation" aria-label="Navigasi utama">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?php echo e(route('home')); ?>" aria-label="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> - Beranda">
                <?php if(config('app.logo')): ?>
                    <img src="<?php echo e(asset(config('app.logo'))); ?>" alt="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> Logo"
                         style="width: 40px; height: 40px; object-fit: contain; border-radius: 12px;"
                         class="brand-logo">
                <?php else: ?>
                    <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-building text-white"></i>
                    </div>
                <?php endif; ?>
                <span class="brand-text"><?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
                    style="border: none; color: var(--bs-primary);">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Menu -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo e(route('home')); ?>" data-lang-key="home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/services" data-lang-key="services">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang" data-lang-key="about">Tentang</a>
                    </li>
                </ul>

                <!-- Center Menu -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('public.visitor.book')); ?>" data-lang-key="visitor-book">Buku Tamu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('supervision.skm.survey')); ?>" data-lang-key="survey">Survei</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('supervision.complaints.dashboard')); ?>" data-lang-key="complaints">Pengaduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('onlineportal.track.ticket.form')); ?>" data-lang-key="track-ticket">Lacak Tiket</a>
                    </li>
                </ul>

                <!-- Right Menu -->
                <ul class="navbar-nav">
                    <!-- Language Dropdown -->
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle d-flex align-items-center border border-secondary rounded px-2 py-1"
                                href="#" id="languageDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i>
                            <span id="currentLang">INA</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li>
                                <button class="dropdown-item d-flex align-items-center" onclick="changeLanguage('id')">
                                    <span class="me-2">🇮🇩</span> Indonesia
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" onclick="changeLanguage('en')">
                                    <span class="me-2">🇬🇧</span> English
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" onclick="changeLanguage('ar')">
                                    <span class="me-2">🇸🇦</span> العربية
                                </button>
                            </li>
                        </ul>
                    </li>

                    <!-- Theme Toggle -->
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center border border-secondary rounded px-2 py-1"
                                onclick="toggleTheme()" title="Toggle theme">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                    </li>

                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('onlineportal.dashboard')); ?>">
                                <i class="fas fa-user-circle me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-link nav-link text-danger">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3" href="<?php echo e(route('login')); ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Hero Section -->
        <section id="hero-section" aria-labelledby="hero-heading">
            <!-- Animated Background Elements -->
            <div class="hero-background" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; z-index: 1;">
                <div class="hero-bg-element" style="position: absolute; top: 20%; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
                <div class="hero-bg-element" style="position: absolute; top: 60%; right: 10%; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; animation: float 8s ease-in-out infinite 2s;"></div>
                <div class="hero-bg-element" style="position: absolute; bottom: 20%; left: 20%; width: 80px; height: 80px; background: rgba(255,255,255,0.08); border-radius: 50%; animation: float 7s ease-in-out infinite 1s;"></div>
            </div>

            <div class="hero-content stagger-fade-in" style="max-width: 900px; padding: 20px; z-index: 2; position: relative;">
                <h1 id="hero-heading" class="hero-title">
                    PTSP MTsN 2 KOTA MALANG
                </h1>

                <div class="hero-subtitle">
                    Pelayanan Terpadu Satu Pintu
                </div>

                <p class="hero-description">
                    Melayani dengan Hati, Cepat, Transparan, dan Akuntabel
                </p>

                <div class="hero-buttons">
                    <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="hero-btn-primary">
                        <i class="fas fa-th-large"></i>
                        Lihat Semua Layanan
                    </a>

                    <a href="<?php echo e(route('onlineportal.track.ticket.form')); ?>" class="hero-btn-secondary">
                        <i class="fas fa-search"></i>
                        Lacak Status Tiket
                    </a>
                </div>

                <div class="hero-info">
                    <div class="info-item">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-clock"></i> 08:00
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Waktu Buka</div>
                    </div>
                    <div class="info-item">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-clock"></i> 15:00
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Waktu Tutup</div>
                    </div>
                    <div class="info-item">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-calendar-week"></i> Senin-Jumat
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Hari Operasional</div>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 2; animation: bounce 2s infinite;">
                <div style="width: 30px; height: 50px; border: 2px solid rgba(255,255,255,0.5); border-radius: 15px; position: relative;">
                    <div style="width: 4px; height: 10px; background: white; border-radius: 2px; position: absolute; top: 8px; left: 50%; transform: translateX(-50%); animation: scroll 2s infinite;"></div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="tentang" aria-labelledby="about-heading">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" data-aos="fade-right">
                        <h2 id="about-heading" class="display-4 fw-bold mb-4">
                            Tentang <span style="color: var(--bs-primary);">PTSP</span> MTsN 2 Kota Malang
                        </h2>
                        <p class="lead mb-4">
                            Pelayanan Terpadu Satu Pintu (PTSP) MTsN 2 Kota Malang adalah sistem pelayanan terpadu yang dirancang untuk memberikan kemudahan akses layanan bagi seluruh sivitas akademika dan masyarakat.
                        </p>
                        <p class="mb-4">
                            Sistem ini dibangun sesuai dengan <strong style="color: var(--bs-primary);">Permen PANRB 15/2014</strong> tentang Pedoman Standar Pelayanan yang menjamin transparansi, akuntabilitas, dan kecepatan pelayanan.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(20, 83, 45, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-check-double" style="color: var(--bs-primary);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Transparan & Akuntabel</h5>
                                        <p class="text-muted mb-0">Setiap proses layanan dapat dilacak secara real-time dengan sistem monitoring terintegrasi</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-clock" style="color: var(--bs-secondary);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Cepat & Tepat Waktu</h5>
                                        <p class="text-muted mb-0">Jaminan penyelesaian sesuai standar waktu layanan dengan SLA yang jelas</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-mobile-alt" style="color: #10b981;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Mudah Diakses</h5>
                                        <p class="text-muted mb-0">Layanan online 24/7 dan offline di loket PTSP dengan antarmuka yang user-friendly</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="p-4 rounded-4" style="background: var(--gradient-primary); color: white;">
                            <h3 class="h2 fw-bold mb-4 text-center">14 Komponen Standar Pelayanan</h3>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-gavel fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Dasar Hukum</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-list-alt fs-3 mb-2 text-info icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Persyaratan</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-sitemap fs-3 mb-2 text-success icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Prosedur</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-clock fs-3 mb-2 text-primary icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Jangka Waktu</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-money-bill-wave fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Biaya/Tarif</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-file-contract fs-3 mb-2 text-danger icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Produk Layanan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <p class="mb-0">+ 8 Komponen Lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Performance Stats Section -->
        <section style="background-color: var(--bs-gray-50);" aria-labelledby="stats-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                         style="background-color: rgba(20, 83, 45, 0.1);">
                        <i class="fas fa-chart-line me-2" style="color: var(--bs-primary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Statistik Kinerja</span>
                    </div>
                    <h2 id="stats-heading" class="display-4 fw-bold mb-3">
                        Kinerja <span style="color: var(--bs-primary);">Pelayanan</span> Kami
                    </h2>
                    <p class="lead text-muted">
                        Transparansi dan Akuntabilitas dalam Setiap Layanan
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Tingkat kepuasan pelanggan 98%">
                            <div class="feature-icon mb-3 mx-auto" style="background: var(--gradient-primary);">
                                <i class="fas fa-smile-beam text-white"></i>
                            </div>
                            <div class="stat-number">98%</div>
                            <h3 class="stat-label">Tingkat Kepuasan</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-chart-line me-2"></i>Berdasarkan SKM 2025
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 98%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Rata-rata waktu penyelesaian 2 hari">
                            <div class="feature-icon mb-3 mx-auto" style="background: var(--gradient-secondary);">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="stat-number">2 Hari</div>
                            <h3 class="stat-label">Rata-rata Waktu</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-hourglass-half me-2"></i>Penyelesaian Layanan
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 85%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                        <article class="stat-card" tabindex="0" role="article" aria-label="15+ jenis layanan tersedia">
                            <div class="feature-icon mb-3 mx-auto" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="fas fa-tasks text-white"></i>
                            </div>
                            <div class="stat-number">15+</div>
                            <h3 class="stat-label">Jenis Layanan</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-clipboard-check me-2"></i>Sesuai Standar
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 100%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Akses online 24/7">
                            <div class="feature-icon mb-3 mx-auto" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="fas fa-globe text-white"></i>
                            </div>
                            <div class="stat-number">24/7</div>
                            <h3 class="stat-label">Akses Online</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-wifi me-2"></i>Kapan Saja, Dimana Saja
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 100%;"></div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Additional Stats Bar -->
                <div class="mt-5 p-4 rounded-3 border-2 border-dashed" style="border-color: var(--bs-primary); background-color: var(--bs-white);" data-aos="fade-up" data-aos-delay="500">
                    <div class="row text-center">
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: var(--bs-primary);">5000+</div>
                            <div class="text-muted small">Layanan Diproses</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: var(--bs-secondary);">100%</div>
                            <div class="text-muted small">Digitalisasi</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: #10b981;">4.8/5</div>
                            <div class="text-muted small">Rating Layanan</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: #3b82f6;">99.9%</div>
                            <div class="text-muted small">Uptime</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="layanan" aria-labelledby="services-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                         style="background-color: rgba(20, 83, 45, 0.1);">
                        <i class="fas fa-th-large me-2" style="color: var(--bs-primary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Layanan Kami</span>
                    </div>
                    <h2 id="services-heading" class="display-4 fw-bold mb-3">
                        Jenis <span style="color: var(--bs-primary);">Pelayanan</span> Tersedia
                    </h2>
                    <p class="lead text-muted">
                        Melayani berbagai kebutuhan sivitas akademika dan masyarakat
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Layanan Akademik -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                        <article class="service-card">
                            <div class="service-header service-header-primary">
                                <div class="service-icon">
                                    <i class="fas fa-user-graduate text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Akademik</h3>
                                <p class="service-subtitle">Untuk Siswa & Alumni</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Keterangan Siswa Aktif</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Legalisir Ijazah & Transkrip</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Rekomendasi</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Keterangan Berkelakuan Baik</span>
                                    </li>
                                </ul>
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Layanan Wali Murid -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <article class="service-card">
                            <div class="service-header service-header-secondary">
                                <div class="service-icon">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Wali Murid</h3>
                                <p class="service-subtitle">Untuk Orang Tua</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Informasi Akademik Anak</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Izin Tidak Masuk Sekolah</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Panggilan Orang Tua</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Konsultasi BK</span>
                                    </li>
                                </ul>
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Layanan Instansi -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                        <article class="service-card">
                            <div class="service-header service-header-emerald">
                                <div class="service-icon">
                                    <i class="fas fa-briefcase text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Instansi</h3>
                                <p class="service-subtitle">Untuk Mitra Kerja</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Permohonan Kerjasama</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Izin Kegiatan & Penelitian</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Permohonan Data Statistik</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Rekomendasi Instansi</span>
                                    </li>
                                </ul>
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Access Section -->
        <section style="background-color: var(--bs-gray-50);" aria-labelledby="access-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 id="access-heading" class="display-4 fw-bold mb-3">
                        Akses <span style="color: var(--bs-primary);">Cepat</span>
                    </h2>
                    <p class="lead text-muted">
                        Layanan penting dalam satu klik
                    </p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto">
                                    <i class="fas fa-file-medical text-white"></i>
                                </div>
                                <h4 class="feature-title">Permohonan Online</h4>
                                <p class="feature-description">
                                    Ajukan layanan secara digital tanpa harus datang ke loket
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Mulai Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <a href="<?php echo e(route('onlineportal.track.ticket.form')); ?>" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: var(--gradient-secondary);">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <h4 class="feature-title">Lacak Tiket</h4>
                                <p class="feature-description">
                                    Cek status permohonan Anda secara real-time
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Track Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <a href="<?php echo e(route('public.visitor.book')); ?>" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                                <h4 class="feature-title">Buku Tamu</h4>
                                <p class="feature-description">
                                    Daftar kunjungan fisik ke sekolah
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Daftar Kunjungan</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <a href="<?php echo e(route('supervision.complaints.dashboard')); ?>" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                    <i class="fas fa-exclamation-circle text-white"></i>
                                </div>
                                <h4 class="feature-title">Pengaduan</h4>
                                <p class="feature-description">
                                    Sampaikan keluhan atau saran untuk perbaikan layanan
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Buat Pengaduan</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                        <a href="<?php echo e(route('supervision.whistleblowing.form')); ?>" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                                    <i class="fas fa-bell text-white"></i>
                                </div>
                                <h4 class="feature-title">Whistleblowing</h4>
                                <p class="feature-description">
                                    Laporkan pelanggaran secara aman dan terlindungi
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Laporkan Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                        <a href="<?php echo e(route('supervision.skm.survey')); ?>" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                    <i class="fas fa-poll text-white"></i>
                                </div>
                                <h4 class="feature-title">Survei SKM</h4>
                                <p class="feature-description">
                                    Bantu kami tingkatkan kualitas pelayanan
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Isi Survei</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
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
                        <?php if(config('app.logo')): ?>
                            <img src="<?php echo e(asset(config('app.logo'))); ?>" alt="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> Logo"
                                 style="width: 48px; height: 48px; object-fit: contain; border-radius: 12px;"
                                 class="footer-logo">
                        <?php else: ?>
                            <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-building text-white"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3><?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?></h3>
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
                        <span class="me-3" style="font-size: 0.8rem; color: var(--text-on-dark-3);">v1.0.0</span>
                        &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?>. Hak Cipta Dilindungi.
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS
            AOS.init({
                duration: 1200,
                once: true,
                offset: 120,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                delay: 0,
                anchorPlacement: 'center-bottom'
            });

            // Theme Management
            function initializeTheme() {
                const savedTheme = localStorage.getItem('theme') || 'light';
                const themeIcon = document.getElementById('themeIcon');

                setTheme(savedTheme);

                // Listen for system theme changes
                if (window.matchMedia) {
                    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                    darkModeQuery.addListener((e) => {
                        if (!localStorage.getItem('theme')) {
                            setTheme(e.matches ? 'dark' : 'light');
                        }
                    });
                }
            }

            function toggleTheme() {
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            }

            function setTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                const themeIcon = document.getElementById('themeIcon');
                if (themeIcon) {
                    themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                }
            }

            // Initialize theme
            initializeTheme();

            // Add event listener to theme toggle button
            const themeToggle = document.querySelector('[onclick="toggleTheme()"]');
            if (themeToggle) {
                themeToggle.addEventListener('click', toggleTheme);
            }

            // Language functions
            function changeLanguage(lang) {
                // Update current language display
                const currentLangSpan = document.getElementById('currentLang');
                if (currentLangSpan) {
                    currentLangSpan.textContent = lang.toUpperCase();
                }

                // Store selected language
                localStorage.setItem('language', lang);

                // Close dropdown
                const dropdown = document.getElementById('languageDropdown');
                if (dropdown) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(dropdown);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }

                // In a real implementation, you would update the page content here
                // For now, we'll just show a notification
                console.log(`Language changed to: ${lang}`);
            }

            // Make changeLanguage globally available
            window.changeLanguage = changeLanguage;
        });
    </script>
</body>
</html><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\welcome_backup.blade.php ENDPATH**/ ?>