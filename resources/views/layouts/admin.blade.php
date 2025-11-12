<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ $themeAdmin ?? 'corporate' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $appName = \App\Models\AppSetting::where('key', 'app_name')->value('value') ?? 'PTSP MTsN 2 KOTA MALANG';
    @endphp

    <title>@yield('title', $appName . ' - Super Admin')</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Vite Assets (includes Bootstrap Icons locally) -->
    @vite(['resources/css/app.css', 'resources/css/bootstrap-custom.css', 'resources/css/admin.css', 'resources/js/bootstrap-bundle.js', 'resources/js/chart-bundle.js', 'resources/js/app.js'])
</head>
<body class="bg-base-200">
    <div class="flex">
        <div class="drawer lg:drawer-open">
            <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
            <div class="drawer-content flex flex-col">
                <!-- Page content here -->
                <div class="navbar bg-base-100 shadow-sm sticky top-0 z-10" role="navigation" aria-label="Top navigation">
                    <div class="flex-none lg:hidden">
                        <label for="my-drawer-2" class="btn btn-square btn-ghost">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </label>
                    </div>
                    <div class="flex-1 px-2 mx-2">
                        <a href="{{ route('suadmin.dashboard') }}" class="text-xl font-bold text-primary">PTSP MTsN 2 KOTA MALANG</a>
                    </div>
                    <div class="flex-none">
                        <!-- Notifications -->
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-circle" role="button" aria-haspopup="true">
                                <div class="indicator">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                    @php
                                        $pendingTicketsCount = \App\Models\Ticket::where('status', 'pending')->count();
                                        $pendingApprovalsCount = \App\Models\Ticket::where('status', 'pending_approval')->count();
                                        $complaintsCount = \App\Models\Complaint::where('status', 'pending')->count();
                                        $totalUnreadNotifications = $pendingTicketsCount + $pendingApprovalsCount + $complaintsCount;
                                    @endphp
                                    @if($totalUnreadNotifications > 0)
                                        <span class="badge badge-xs badge-error indicator-item">{{ $totalUnreadNotifications }}</span>
                                    @endif
                                </div>
                            </label>
                            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-72" role="menu">
                                @if($pendingTicketsCount > 0)
                                    <li>
                                        <a href="{{ route('suadmin.tickets.index') }}?status=pending" role="menuitem">
                                            <div class="flex items-center">
                                                <div class="mr-3 p-2 rounded-full bg-warning/10 text-warning">
                                                    <i class="bi bi-ticket-perforated"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium">{{ $pendingTicketsCount }} Tiket Baru</div>
                                                    <div class="text-xs text-base-content/70">Menunggu verifikasi</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if($pendingApprovalsCount > 0)
                                    <li>
                                        <a href="{{ route('suadmin.tickets.index') }}?status=pending_approval" role="menuitem">
                                            <div class="flex items-center">
                                                <div class="mr-3 p-2 rounded-full bg-primary/10 text-primary">
                                                    <i class="bi bi-check-circle"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium">{{ $pendingApprovalsCount }} Persetujuan Menunggu</div>
                                                    <div class="text-xs text-base-content/70">Menunggu approval</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if($complaintsCount > 0)
                                    <li>
                                        <a href="{{ route('suadmin.complaints.index') }}?status=pending" role="menuitem">
                                            <div class="flex items-center">
                                                <div class="mr-3 p-2 rounded-full bg-error/10 text-error">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium">{{ $complaintsCount }} Pengaduan Masuk</div>
                                                    <div class="text-xs text-base-content/70">Menunggu ditanggapi</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if($totalUnreadNotifications === 0)
                                    <li><span class="text-center text-base-content/70" role="menuitem">Tidak ada notifikasi baru</span></li>
                                @endif
                            </ul>
                        </div>
                        <!-- User Dropdown -->
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-circle avatar" role="button" aria-haspopup="true">
                                <div class="w-10 rounded-full">
                                    <img src="{{ auth()->user()->avatar && str_contains(auth()->user()->avatar, 'ui-avatars.com') ? auth()->user()->avatar : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=amber' }}" />
                                </div>
                            </label>
                            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52" role="menu">
                                <li>
                                    <a class="justify-between" href="{{ route('profile.edit') }}" role="menuitem">
                                        Profil Saya
                                        <span class="badge">New</span>
                                    </a>
                                </li>
                                <li>
                                    <a role="menuitem">Pengaturan</a>
                                    <ul class="p-2" role="menu">
                                        <li><a class="theme-selector" data-theme="light" role="menuitem">Terang</a></li>
                                        <li><a class="theme-selector" data-theme="dark" role="menuitem">Gelap</a></li>
                                        <li><a class="theme-selector" data-theme="auto" role="menuitem">Sistem</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" role="menuitem">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <main class="p-4">
                    @yield('content')
                </main>
                <footer class="footer footer-center p-4 bg-base-300 text-base-content">
                    <aside>
                        <p>v1.0.0 © 2025 PTSP MTsN 2 KOTA MALANG. Hak Cipta Dilindungi. | Dikembangkan dengan ❤️ oleh Tim PUSKOM</p>
                    </aside>
                </footer>
            </div> 
            <div class="drawer-side">
                <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label> 
                <ul class="menu p-4 w-80 min-h-full bg-base-200 text-base-content" role="navigation" aria-label="Main navigation">
                    <!-- Sidebar content here -->
                    <li class="menu-title">Menu</li>
                    <li><a class="@if(request()->routeIs('suadmin.dashboard')) active @endif" href="{{ route('suadmin.dashboard') }}" role="menuitem"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="menu-title">Master Data</li>
                    <li><a class="@if(request()->routeIs('suadmin.services.*')) active @endif" href="{{ route('suadmin.services.index') }}" role="menuitem"><i class="bi bi-cone-striped"></i> Layanan</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.service-categories.*')) active @endif" href="{{ route('suadmin.service-categories.index') }}" role="menuitem"><i class="bi bi-tags"></i> Kategori Layanan</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.pengumuman.*')) active @endif" href="{{ route('suadmin.pengumuman.index') }}" role="menuitem"><i class="bi bi-megaphone"></i> Pengumuman</a></li>
                    <li class="menu-title">Manajemen Tiket</li>
                    <li><a class="@if(request()->routeIs('suadmin.tickets.*')) active @endif" href="{{ route('suadmin.tickets.index') }}" role="menuitem"><i class="bi bi-ticket-detailed"></i> Tiket Layanan</a></li>
                    <li class="menu-title">Pengunjung</li>
                    <li><a class="@if(request()->routeIs('suadmin.visitors.*')) active @endif" href="{{ route('suadmin.visitors.index') }}" role="menuitem"><i class="bi bi-person-walking"></i> Buku Tamu</a></li>
                    <li class="menu-title">Pengaduan</li>
                    <li><a class="@if(request()->routeIs('suadmin.complaints.*') && !request()->routeIs('suadmin.whistleblowing.*')) active @endif" href="{{ route('suadmin.complaints.index') }}" role="menuitem"><i class="bi bi-chat-left-text"></i> Pengaduan Masyarakat</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.whistleblowing.*')) active @endif" href="{{ route('suadmin.whistleblowing.index') }}" role="menuitem"><i class="bi bi-shield-exclamation"></i> Whistleblowing</a></li>
                    <li class="menu-title">Pengawasan & Evaluasi</li>
                    <li><a class="@if(request()->routeIs('suadmin.survey.management')) active @endif" href="{{ route('suadmin.survey.management') }}" role="menuitem"><i class="bi bi-clipboard-check"></i> Manajemen Survey</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.skm.*')) active @endif" href="{{ route('suadmin.skm.report') }}" role="menuitem"><i class="bi bi-graph-up"></i> Laporan SKM</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.spak.*')) active @endif" href="{{ route('suadmin.spak.report') }}" role="menuitem"><i class="bi bi-shield-check"></i> Laporan SPAK</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.performance.*')) active @endif" href="{{ route('suadmin.performance.report') }}" role="menuitem"><i class="bi bi-bar-chart-line"></i> Laporan Kinerja</a></li>
                    <li class="menu-title">Manajemen Pengguna</li>
                    <li><a class="@if(request()->routeIs('suadmin.users.*')) active @endif" href="{{ route('suadmin.users.index') }}" role="menuitem"><i class="bi bi-people"></i> Manajemen User</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.roles.*')) active @endif" href="{{ route('suadmin.roles.index') }}" role="menuitem"><i class="bi bi-person-check"></i> Manajemen Role</a></li>
                    <li class="menu-title">Keamanan</li>
                    <li><a class="@if(request()->routeIs('suadmin.security.*')) active @endif" href="{{ route('suadmin.security.dashboard') }}" role="menuitem"><i class="bi bi-shield-lock"></i> Security Dashboard</a></li>
                    <li class="menu-title">Konfigurasi</li>
                    <li><a class="@if(request()->routeIs('suadmin.settings.*')) active @endif" href="{{ route('suadmin.settings.index') }}" role="menuitem"><i class="bi bi-gear"></i> Pengaturan Umum</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.roles.*')) active @endif" href="{{ route('suadmin.roles.index') }}" role="menuitem"><i class="bi bi-person-check"></i> Hak Akses</a></li>
                    <li><a class="@if(request()->routeIs('suadmin.users.*')) active @endif" href="{{ route('suadmin.users.index') }}" role="menuitem"><i class="bi bi-people"></i> Manajemen User</a></li>
                </ul>
            </div>
        </div>
    </div>