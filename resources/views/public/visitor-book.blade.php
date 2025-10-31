<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - PTSP MTsN 2 Kota Malang</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-primary: #2563eb;
            --bs-primary-rgb: 37, 99, 235;
        }

        .navbar {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
        }

        .dark .navbar {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%) !important;
        }

        .border-secondary {
            border-color: rgba(255, 255, 255, 0.3) !important;
        }

        .dropdown-menu {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .dark .dropdown-menu {
            background: rgba(30, 58, 138, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dark .dropdown-item {
            color: #fff;
        }

        .dark .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* RTL Support for Arabic */
        body.rtl {
            direction: rtl;
            text-align: right;
        }

        body.rtl .navbar-nav {
            padding-right: 0;
        }

        body.rtl .dropdown-menu {
            right: 0;
            left: auto;
            text-align: right;
        }

        body.rtl .ml-auto {
            margin-left: 0;
            margin-right: auto;
        }

        body.rtl .me-2 {
            margin-right: 0;
            margin-left: 0.5rem;
        }

        body.rtl .me-3 {
            margin-right: 0;
            margin-left: 1rem;
        }

        body.rtl .text-center {
            text-align: center !important;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-900 shadow-lg px-4">
        <div class="container-fluid px-4">
            <!-- Logo and App Name (Left) -->
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <i class="fas fa-building text-2xl me-3"></i>
                <div>
                    <h1 class="h5 mb-0 fw-bold">PTSP MTsN 2 Kota Malang</h1>
                    <small class="text-white-50 d-block" style="font-size: 0.75rem;">Pelayanan Terpadu Satu Pintu</small>
                </div>
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu (Center and Right) -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Menu -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('home') }}" data-lang-key="home">Beranda</a>
                    </li>
                </ul>

                <!-- Center Menu -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.visitor.book') }}" data-lang-key="visitor-book">Buku Tamu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.about') }}" data-lang-key="about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.contact') }}" data-lang-key="contact">Kontak</a>
                    </li>
                </ul>

                <!-- Right Menu -->
                <ul class="navbar-nav">
                    <!-- Language Dropdown -->
                    <li class="nav-item dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center border border-secondary rounded px-2 py-1 me-2"
                                type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i>
                            <span id="currentLang">INA</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="#" onclick="changeLanguage('ina')">
                                🇮🇩 Indonesia
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeLanguage('eng')">
                                🇬🇧 English
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeLanguage('arb')">
                                🇸🇦 العربية
                            </a></li>
                        </ul>
                    </li>

                    <!-- Theme Toggle Button -->
                    <li class="nav-item">
                        <button id="themeToggle" class="btn btn-sm btn-outline-light d-flex align-items-center border border-secondary rounded px-2 py-1 me-2"
                                type="button" title="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>

                    <!-- Login Button -->
                    <li class="nav-item">
                        <a class="btn btn-sm btn-light text-blue-600 fw-semibold" href="{{ route('login') }}" data-lang-key="login">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4" data-lang-key="hero-title">
                    <i class="fas fa-book-open mr-3 text-blue-600 dark:text-blue-400"></i>
                    Buku Tamu Digital
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto" data-lang-key="hero-subtitle">
                    Sistem pencatatan tamu digital yang modern dan efisien untuk monitoring kunjungan di PTSP MTsN 2 Kota Malang
                </p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                        <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400" data-lang-key="total-today">Total Tamu Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $visitors->total() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                        <i class="fas fa-clock text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400" data-lang-key="active-now">Sedang Aktif</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $visitors->whereNull('checkout_at')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                        <i class="fas fa-calendar-check text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-list mr-2"></i>Daftar Tamu
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Tamu
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Instansi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Tujuan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Check In
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Check Out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($visitors as $index => $visitor)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ($visitors->currentPage() - 1) * $visitors->perPage() + $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $visitor->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $visitor->phone }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $visitor->institution }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $visitor->purpose }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($visitor->created_at)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $visitor->checkout_at ? \Carbon\Carbon::parse($visitor->checkout_at)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($visitor->checkout_at)
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                            <i class="fas fa-circle text-xs mr-1"></i>Aktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Belum ada data tamu untuk tanggal {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($visitors->hasPages())
                <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $visitors->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan <span class="font-medium">{{ $visitors->firstItem() }}</span>
                                hingga <span class="font-medium">{{ $visitors->lastItem() }}</span>
                                dari <span class="font-medium">{{ $visitors->total() }}</span> hasil
                            </p>
                        </div>
                        <div>
                            {{ $visitors->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Tentang PTSP -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">PTSP MTsN 2 Kota Malang</h3>
                    <p class="text-gray-400 text-sm">
                        Pelayanan Terpadu Satu Pintu MTsN 2 Kota Malang berkomitmen memberikan pelayanan terbaik untuk masyarakat.
                    </p>
                </div>

                <!-- Link Terkait -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Link Terkait</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="{{ route('onlineportal.service.catalog') }}" class="hover:text-white transition-colors">Katalog Layanan</a></li>
                        <li><a href="{{ route('supervision.complaints.dashboard') }}" class="hover:text-white transition-colors">Pengaduan</a></li>
                        <li><a href="{{ route('supervision.skm.survey') }}" class="hover:text-white transition-colors">Survei Kepuasan</a></li>
                        <li><a href="{{ route('public.visitor.book') }}" class="hover:text-white transition-colors">Buku Tamu</a></li>
                    </ul>
                </div>

                <!-- Kontak Kami -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Kontak Kami</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-2 text-orange-500"></i>
                            <span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone mt-1 mr-2 text-orange-500"></i>
                            <span>(0341) 711500</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope mt-1 mr-2 text-orange-500"></i>
                            <span>mtsnmalang2adm@gmail.com</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-globe mt-1 mr-2 text-orange-500"></i>
                            <span>www.mtsn2kotamalang.sch.id</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fab fa-whatsapp mt-1 mr-2 text-orange-500"></i>
                            <span>0851 8336 7500 (PTSP)</span>
                        </li>
                    </ul>
                </div>

                <!-- Jam Operasional -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Jam Operasional</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><i class="far fa-clock mr-2"></i>Senin - Jumat: 07:30 - 15:00</li>
                        <li><i class="far fa-clock mr-2"></i>Sabtu: 07:30 - 12:00</li>
                        <li><i class="far fa-clock mr-2 text-red-500"></i>Minggu & Libur: Tutup</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>© 2025 PTSP MTsN 2 Kota Malang. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Translation dictionary
        const translations = {
            'ina': {
                'home': 'Beranda',
                'visitor-book': 'Buku Tamu',
                'about': 'Tentang',
                'contact': 'Kontak',
                'login': 'Login',
                'hero-title': 'Buku Tamu Digital',
                'hero-subtitle': 'Sistem pencatatan tamu digital yang modern dan efisien untuk monitoring kunjungan di PTSP MTsN 2 Kota Malang',
                'visitor-list': 'Daftar Tamu',
                'total-today': 'Total Tamu Hari Ini',
                'active-now': 'Sedang Aktif',
                'date': 'Tanggal',
                'no-visitors': 'Belum ada data tamu untuk tanggal',
                'finished': 'Selesai',
                'active': 'Aktif'
            },
            'eng': {
                'home': 'Home',
                'visitor-book': 'Visitor Book',
                'about': 'About',
                'contact': 'Contact',
                'login': 'Login',
                'hero-title': 'Digital Visitor Book',
                'hero-subtitle': 'Modern and efficient digital visitor recording system for monitoring visits at PTSP MTsN 2 Kota Malang',
                'visitor-list': 'Visitor List',
                'total-today': 'Total Visitors Today',
                'active-now': 'Currently Active',
                'date': 'Date',
                'no-visitors': 'No visitor data for',
                'finished': 'Completed',
                'active': 'Active'
            },
            'arb': {
                'home': 'الرئيسية',
                'visitor-book': 'سجل الزوار',
                'about': 'حول',
                'contact': 'اتصل',
                'login': 'تسجيل الدخول',
                'hero-title': 'سجل الزوار الرقمي',
                'hero-subtitle': 'نظام حديث وفعال لتسجيل الزوار الرقمي لمراقبة الزيارات في PTSP MTsN 2 كوتا مالانج',
                'visitor-list': 'قائمة الزوار',
                'total-today': 'إجمالي الزوار اليوم',
                'active-now': 'نشط حاليا',
                'date': 'التاريخ',
                'no-visitors': 'لا توجد بيانات زوار لـ',
                'finished': 'مكتمل',
                'active': 'نشط'
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize language from localStorage
            const currentLang = localStorage.getItem('language') || 'ina';
            updateLanguage(currentLang);

            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;
            const currentTheme = localStorage.getItem('theme') || 'light';

            html.setAttribute('data-bs-theme', currentTheme);
            updateThemeIcon(currentTheme);

            themeToggle.addEventListener('click', function() {
                const currentTheme = html.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                html.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });

            function updateThemeIcon(theme) {
                const icon = themeToggle.querySelector('i');
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        });

        // Change language function
        function changeLanguage(lang) {
            localStorage.setItem('language', lang);
            updateLanguage(lang);
        }

        function updateLanguage(lang) {
            // Update current language display
            const currentLangSpan = document.getElementById('currentLang');
            if (currentLangSpan) {
                currentLangSpan.textContent = lang.toUpperCase();
            }

            // Update RTL for Arabic
            const html = document.documentElement;
            if (lang === 'arb') {
                html.setAttribute('dir', 'rtl');
                html.setAttribute('lang', 'ar');
                document.body.classList.add('rtl');
            } else {
                html.setAttribute('dir', 'ltr');
                html.setAttribute('lang', lang === 'eng' ? 'en' : 'id');
                document.body.classList.remove('rtl');
            }

            // Update all elements with data-lang-key
            const elements = document.querySelectorAll('[data-lang-key]');
            elements.forEach(element => {
                const key = element.getAttribute('data-lang-key');
                const translation = translations[lang][key];
                if (translation) {
                    if (element.tagName === 'INPUT' && element.type === 'submit') {
                        element.value = translation;
                    } else {
                        element.textContent = translation;
                    }
                }
            });
        }
    </script>
</body>
</html>