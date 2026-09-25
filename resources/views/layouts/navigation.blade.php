<nav class="bg-base-100 shadow-sm sticky top-0 z-50" role="navigation" aria-label="Navigasi utama">
    <div class="max-w-full mx-auto px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Left Section: Burger Menu + Logo & App Name -->
            <div class="flex items-center gap-4">
                <!-- Burger Menu (Mobile/Tablet) -->
                <div class="dropdown lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-100 mt-3 w-52 p-2 shadow">
                        <li><a href="{{ route('home') }}" class="text-gray-900 font-semibold"><i class="fas fa-home mr-2"></i>Beranda</a></li>
                        <li><a href="{{ route('onlineportal.service.catalog') }}" class="text-gray-900 font-semibold"><i class="fas fa-concierge-bell mr-2"></i>Layanan</a></li>
                        <li><a href="{{ route('public.about') }}" class="text-gray-900 font-semibold"><i class="fas fa-info-circle mr-2"></i>Tentang</a></li>
                        <li><a href="{{ route('public.visitor.book') }}" class="text-gray-900 font-semibold"><i class="fas fa-book mr-2"></i>Buku Tamu</a></li>
                        <li><a href="{{ route('survey.form') }}" class="text-gray-900 font-semibold"><i class="fas fa-poll mr-2"></i>Survei</a></li>
                        <li><a href="{{ route('supervision.complaints.dashboard') }}" class="text-gray-900 font-semibold"><i class="fas fa-comments mr-2"></i>Pengaduan</a></li>
                        <li><a href="{{ route('onlineportal.track.ticket.form') }}" class="text-gray-900 font-semibold"><i class="fas fa-search mr-2"></i>Lacak Tiket</a></li>
                        <li><a href="{{ route('pengumuman.index') }}" class="text-gray-900 font-semibold"><i class="fas fa-bullhorn mr-2"></i>Pengumuman</a></li>
                    </ul>
                </div>

                <!-- Logo & App Name -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
                    @if(config('app.logo'))
                        <img src="{{ asset(config('app.logo')) }}" alt="Logo" class="h-8 w-8 object-contain" />
                    @else
                        <img src="{{ asset('images/kemenag-logo.png') }}" alt="Logo" class="h-8 w-8 object-contain" />
                    @endif
                    <span class="hidden sm:inline font-bold text-lg text-gray-900">{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}</span>
                </a>
            </div>

            <!-- Center Section: Menu Horizontal (Desktop) -->
            <div class="hidden lg:flex items-center gap-1">
                <a href="{{ route('home') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Beranda</a>
                <a href="{{ route('onlineportal.service.catalog') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Layanan</a>
                <a href="{{ route('public.about') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Tentang</a>
                <a href="{{ route('public.visitor.book') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Buku Tamu</a>
                <a href="{{ route('survey.form') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Survei</a>
                <a href="{{ route('supervision.complaints.dashboard') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Pengaduan</a>
                <a href="{{ route('onlineportal.track.ticket.form') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Lacak Tiket</a>
                <a href="{{ route('pengumuman.index') }}" class="px-3 py-2 rounded-md text-gray-900 font-semibold no-underline hover:text-green-600 hover:bg-green-50 transition">Pengumuman</a>
            </div>

            <!-- Right Section: Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Language Dropdown -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-circle btn-sm border-2 border-gray-300 bg-white hover:border-gray-900 hover:bg-gray-50 transition" aria-label="Pilih bahasa">
                        <i class="fas fa-globe text-gray-900"></i>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu menu-sm p-2 shadow bg-base-100 rounded-box w-52 mt-4 z-100">
                        <li><a onclick="changeLanguage('id')"><span>🇮🇩</span>Indonesia</a></li>
                        <li><a onclick="changeLanguage('en')"><span>🇬🇧</span>English</a></li>
                        <li><a onclick="changeLanguage('ar')"><span>🇸🇦</span>العربية</a></li>
                    </ul>
                </div>

                <!-- Theme Toggle -->
                <label class="swap swap-rotate btn btn-circle btn-sm border-2 border-gray-300 bg-white hover:border-gray-900 hover:bg-gray-50 relative inline-flex transition cursor-pointer" aria-label="Toggle tema">
                    <input type="checkbox" id="theme-toggle" class="absolute opacity-0" />
                    <svg class="swap-off fill-current w-4 h-4 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-900" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/></svg>
                    <svg class="swap-on fill-current w-4 h-4 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-900" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/></svg>
                </label>

                <!-- User Menu / Login -->
                @auth
                    @php
                        $user = Auth::user();
                        $dashboardLink = get_dashboard_route_for_user($user);
                    @endphp
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle btn-sm">
                            <div class="w-8 h-8 rounded-full bg-transparent text-gray-900 flex items-center justify-center">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu menu-sm p-2 shadow bg-base-100 rounded-box w-52 mt-4 z-100">
                            <li class="menu-title"><span>{{ $user->name }}</span></li>
                            <li><a href="{{ $dashboardLink }}"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="header-logout-form">
                                    @csrf
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('header-logout-form').submit();" class="text-error">
                                        <i class="fas fa-sign-out-alt"></i>Keluar
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm bg-gray-900 text-white border-2 border-gray-900 hover:bg-gray-700 hover:border-gray-700 transition">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline btn-sm border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white transition">
                        <i class="fas fa-user-plus"></i>
                        <span>Daftar</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
// Initialize theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.checked = savedTheme === 'dark';

        // Add event listener to toggle
        themeToggle.addEventListener('change', function() {
            const newTheme = this.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
});

function changeLanguage(lang) {
    localStorage.setItem('language', lang);
    updateLanguage(lang);
}

function updateLanguage(lang) {
    // Update all elements with data-lang-key
    const elements = document.querySelectorAll('[data-lang-key]');
    elements.forEach(element => {
        const key = element.getAttribute('data-lang-key');
        if (translations[lang] && translations[lang][key]) {
            element.textContent = translations[lang][key];
        }
    });
}
</script>

<style>
/* Specific fixes for header button alignment to counteract page-specific CSS overrides */
nav .btn-circle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    line-height: 1 !important;
}

nav .btn-circle i,
nav .btn-circle svg {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: auto !important;
}

/* Ensure proper border alignment */
nav .btn.btn-circle {
    border: 2px solid !important;
    box-sizing: border-box !important;
}

/* Fix for theme toggle alignment */
.swap-rotate {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Avatar button specific styles */
nav .dropdown .btn.btn-circle {
    background: transparent !important;
}

nav .dropdown .btn.btn-circle div.w-8.h-8.rounded-full {
    background: transparent !important;
    color: #111827 !important;
}

/* Dark mode avatar button styles */
[data-theme="dark"] nav .dropdown .btn.btn-circle div.w-8.h-8.rounded-full {
    color: white !important;
}
</style>
