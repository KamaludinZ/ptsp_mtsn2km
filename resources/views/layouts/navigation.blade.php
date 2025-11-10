<nav class="navbar navbar-expand-lg sticky-top" role="navigation" aria-label="Navigasi utama">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Logo/Brand -->
        <a class="navbar-brand me-auto" href="{{ route('home') }}" aria-label="{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }} - Beranda">
            @if(config('app.logo'))
                <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }} Logo"
                     style="width: 40px; height: 40px; object-fit: contain; border-radius: 12px;"
                     class="brand-logo">
            @else
                <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-building text-white"></i>
                </div>
            @endif
            <span class="brand-text d-none d-sm-inline-block">{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}</span>
        </a>

        <!-- Action Buttons (Always Visible) -->
        <div class="d-flex align-items-center gap-2 order-lg-3">
            <!-- Language Dropdown -->
            <div class="dropdown">
                <button class="btn btn-icon-square" id="languageDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false"
                        aria-label="Pilih bahasa"
                        title="Pilih Bahasa">
                    <i class="fas fa-globe"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="languageDropdown">
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
            </div>

            <!-- Theme Toggle -->
            <button class="btn btn-icon-square" onclick="toggleTheme()"
                    aria-label="Toggle tema gelap/terang"
                    title="Toggle Tema">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>

            <!-- Auth Buttons -->
            @auth
                @php
                    $user = Auth::user();
                    $dashboardLink = get_dashboard_route_for_user($user);
                @endphp
                <!-- User Profile -->
                <a class="btn btn-icon-square d-none d-sm-flex"
                   href="{{ $dashboardLink }}"
                   aria-label="Dashboard"
                   title="Dashboard">
                    <i class="fas fa-user-circle"></i>
                </a>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="d-inline" id="header-logout-form">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('header-logout-form').submit();"
                       class="btn btn-icon-square btn-danger-outline d-none d-sm-flex"
                       aria-label="Keluar"
                       title="Keluar">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </form>
            @else
                <!-- Login Button -->
                <a class="btn btn-sm btn-primary d-none d-md-inline-flex align-items-center"
                   href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <span>Masuk</span>
                </a>

                <!-- Login Icon Only (Tablet) -->
                <a class="btn btn-icon-square d-md-none"
                   href="{{ route('login') }}"
                   aria-label="Masuk"
                   title="Masuk">
                    <i class="fas fa-sign-in-alt"></i>
                </a>

                <!-- Register Button -->
                <a class="btn btn-sm btn-outline-primary d-none d-md-inline-flex align-items-center"
                   href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-2"></i>
                    <span>Daftar</span>
                </a>

                <!-- Register Icon Only (Tablet) -->
                <a class="btn btn-icon-square d-md-none"
                   href="{{ route('register') }}"
                   aria-label="Daftar"
                   title="Daftar">
                    <i class="fas fa-user-plus"></i>
                </a>
            @endauth

            <!-- Burger Menu Toggle -->
            <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Collapsible Menu -->
        <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}" data-lang-key="home">
                        <i class="fas fa-home d-lg-none me-2"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('onlineportal.service.catalog') }}" data-lang-key="services">
                        <i class="fas fa-concierge-bell d-lg-none me-2"></i>
                        <span>Layanan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('public.about') }}" data-lang-key="about">
                        <i class="fas fa-info-circle d-lg-none me-2"></i>
                        <span>Tentang</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('public.visitor.book') }}" data-lang-key="visitor-book">
                        <i class="fas fa-book d-lg-none me-2"></i>
                        <span>Buku Tamu</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('supervision.skm.survey') }}" data-lang-key="survey">
                        <i class="fas fa-poll d-lg-none me-2"></i>
                        <span>Survei</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('supervision.complaints.dashboard') }}" data-lang-key="complaints">
                        <i class="fas fa-comments d-lg-none me-2"></i>
                        <span>Pengaduan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('onlineportal.track.ticket.form') }}" data-lang-key="track-ticket">
                        <i class="fas fa-search d-lg-none me-2"></i>
                        <span>Lacak Tiket</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pengumuman.index') }}" data-lang-key="announcements">
                        <i class="fas fa-bullhorn d-lg-none me-2"></i>
                        <span>Pengumuman</span>
                    </a>
                </li>

                <!-- Mobile Only Auth Links -->
                @auth
                    <li class="nav-item d-sm-none">
                        <hr class="dropdown-divider my-2">
                    </li>
                    <li class="nav-item d-sm-none">
                        <a class="nav-link" href="{{ $dashboardLink }}">
                            <i class="fas fa-user-circle me-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item d-sm-none">
                        <form method="POST" action="{{ route('logout') }}" id="mobile-logout-form">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();"
                               class="nav-link text-danger w-100 text-start">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                <span>Keluar</span>
                            </a>
                        </form>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Navbar Styles */
    .navbar {
        background: var(--bs-white);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 0.75rem 0;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .navbar {
        background: var(--bs-surface);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    /* Brand */
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        color: var(--bs-primary);
        font-size: 1rem;
        padding: 0;
    }

    .brand-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    @media (min-width: 768px) {
        .brand-text {
            max-width: none;
        }
    }

    /* Icon Square Buttons */
    .btn-icon-square {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--bs-border);
        border-radius: 8px;
        background: transparent;
        color: var(--bs-text);
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .btn-icon-square:hover {
        background: var(--bs-surface);
        border-color: var(--bs-primary);
        color: var(--bs-primary);
        transform: translateY(-2px);
    }

    .btn-icon-square:active {
        transform: translateY(0);
    }

    .btn-danger-outline {
        border-color: #dc3545;
        color: #dc3545;
    }

    .btn-danger-outline:hover {
        background: #dc3545;
        color: white;
    }

    /* Navbar Toggler */
    .navbar-toggler {
        border: 1px solid var(--bs-border);
        border-radius: 8px;
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        transition: all 0.2s ease;
    }

    .navbar-toggler:hover {
        background: var(--bs-surface);
        border-color: var(--bs-primary);
    }

    .navbar-toggler:focus {
        box-shadow: none;
        border-color: var(--bs-primary);
    }

    .navbar-toggler-icon {
        width: 20px;
        height: 20px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 0, 0, 0.55)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    [data-theme="dark"] .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* Nav Links */
    .nav-link {
        color: var(--bs-text);
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
        border-radius: 8px;
        display: flex;
        align-items: center;
    }

    .nav-link:hover {
        color: var(--bs-primary);
        background: rgba(20, 83, 45, 0.05);
    }

    [data-theme="dark"] .nav-link:hover {
        background: rgba(20, 83, 45, 0.2);
    }

    /* Mobile Menu Styles */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--bs-white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] .navbar-collapse {
            background: var(--bs-surface);
        }

        .navbar-nav {
            gap: 0.25rem;
        }

        .nav-item {
            width: 100%;
        }

        .nav-link {
            padding: 0.75rem 1rem;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }
    }

    /* Dropdown Menu */
    .dropdown-menu {
        border: 1px solid var(--bs-border);
        border-radius: 12px;
        padding: 0.5rem;
        margin-top: 0.5rem;
        min-width: 180px;
    }

    [data-theme="dark"] .dropdown-menu {
        background: var(--bs-surface);
    }

    .dropdown-item {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: rgba(20, 83, 45, 0.05);
        color: var(--bs-primary);
    }

    [data-theme="dark"] .dropdown-item:hover {
        background: rgba(20, 83, 45, 0.2);
    }

    /* Auth Buttons */
    .btn-primary {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: var(--bs-primary-dark);
        border-color: var(--bs-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(20, 83, 45, 0.3);
    }

    .btn-outline-primary {
        border: 1px solid var(--bs-primary);
        color: var(--bs-primary);
        background: transparent;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-outline-primary:hover {
        background: var(--bs-primary);
        color: white;
        transform: translateY(-2px);
    }

    /* Responsive Breakpoints */
    @media (max-width: 575.98px) {
        .navbar-brand {
            font-size: 0.9rem;
        }

        .btn-icon-square {
            width: 36px;
            height: 36px;
        }

        .navbar-toggler {
            width: 36px;
            height: 36px;
        }
    }

    /* Smooth Animations */
    .navbar-collapse {
        transition: all 0.3s ease-in-out;
    }

    .collapsing {
        transition: height 0.3s ease;
    }
</style>
<!-- Fixed visitor-book CSS conflicts at Mon, Nov  3, 2025  6:55:00 AM -->
