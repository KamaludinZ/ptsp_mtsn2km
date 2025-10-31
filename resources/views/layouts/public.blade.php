<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PTSP MTsN 2 Kota Malang')</title>
    <!-- Build Tailwind CSS with Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center py-4">
                <!-- Logo/Title at top -->
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-building text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">PTSP MTsN 2 Kota Malang</h1>
                        <p class="text-xs text-blue-100">Pelayanan Terpadu Satu Pintu</p>
                    </div>
                </div>
                
                <!-- Centered Navigation -->
                <nav class="hidden md:flex items-center justify-center space-x-6">
                    <a href="{{ route('home') }}" class="hover:text-blue-200 transition-colors">Beranda</a>
                    <a href="{{ route('onlineportal.service.catalog') }}" class="hover:text-blue-200 transition-colors {{ request()->routeIs('onlineportal.service.*') ? 'text-blue-200 font-semibold' : '' }}">Layanan</a>
                    <a href="{{ route('public.visitor.book') }}" class="hover:text-blue-200 transition-colors">Buku Tamu</a>
                    <a href="{{ route('public.about') }}" class="hover:text-blue-200 transition-colors">Tentang</a>
                    <a href="{{ route('public.contact') }}" class="hover:text-blue-200 transition-colors">Kontak</a>
                    @auth
                        <a href="{{ route('onlineportal.dashboard') }}" class="hover:text-blue-200 transition-colors">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors font-semibold flex items-center border border-blue-300 hover:border-blue-400">
                            <i class="fas fa-sign-in-alt mr-2 text-blue-600"></i> Masuk
                        </a>
                    @endauth
                </nav>
                
                <!-- Dark mode toggle and Mobile menu button -->
                <div class="flex justify-between w-full mt-2">
                    <button id="theme-toggle" class="p-2 rounded-lg bg-blue-700 hover:bg-blue-600 text-white transition-colors">
                        <i id="theme-icon" class="fas fa-moon"></i>
                    </button>
                    <button class="md:hidden self-end" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobileMenu" class="hidden md:hidden pb-4">
                <a href="{{ route('home') }}" class="block py-2 hover:text-blue-200">Beranda</a>
                <a href="{{ route('onlineportal.service.catalog') }}" class="block py-2 hover:text-blue-200 {{ request()->routeIs('onlineportal.service.*') ? 'text-blue-200 font-semibold' : '' }}">Layanan</a>
                <a href="{{ route('public.visitor.book') }}" class="block py-2 hover:text-blue-200">Buku Tamu</a>
                <a href="{{ route('public.about') }}" class="block py-2 hover:text-blue-200">Tentang</a>
                <a href="{{ route('public.contact') }}" class="block py-2 hover:text-blue-200">Kontak</a>
                @auth
                    <a href="{{ route('onlineportal.dashboard') }}" class="block py-2 hover:text-blue-200">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block py-2 bg-white text-blue-600 px-4 py-2 rounded-lg text-center font-semibold">
                        <i class="fas fa-sign-in-alt mr-2 text-blue-600"></i> Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
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

    <!-- Scripts -->
    @stack('scripts')

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Auto-hide mobile menu on window resize
        window.addEventListener('resize', () => {
            const menu = document.getElementById('mobileMenu');
            if (window.innerWidth >= 768) {
                menu.classList.add('hidden');
            }
        });

        // Dark mode toggle
        const html = document.documentElement;
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');

        // Check for saved theme preference or respect system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        } else {
            html.classList.remove('dark');
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }

        // Theme toggle function
        function toggleTheme() {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.theme = 'light';
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            } else {
                html.classList.add('dark');
                localStorage.theme = 'dark';
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        }

        // Add event listener to theme toggle button
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Form validation for service applications
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
                }
            });
        });
    </script>

    @livewireStyles
    @livewireScripts
</body>
</html>