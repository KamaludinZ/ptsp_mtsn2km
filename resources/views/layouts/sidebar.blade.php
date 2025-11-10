<!-- Sidebar Navigation -->
<!-- Toggle Button (Mobile) -->
<button class="sidebar-toggle d-lg-none" id="sidebarToggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-primary bg-opacity-10 me-3">
                    <span class="fw-bold" style="color: var(--bs-primary);">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold">{{ Auth::user()->name }}</h6>
                    <small class="text-muted">{{ ucfirst(Auth::user()->user_type) }}</small>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="sidebar-nav">
            @php
                $user = Auth::user();
            @endphp

            <!-- Dashboard (All Roles) -->
            <a href="{{ get_dashboard_route_for_user($user) }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt me-3"></i>
                <span>Dashboard</span>
            </a>


            <!-- SUPER ADMIN & ADMIN MENU -->
            @if($user->hasRole(['super_admin', 'admin']))
                <div class="sidebar-divider">
                    <span>ADMIN</span>
                </div>

                <a href="{{ route('admin.services.index') }}" class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-concierge-bell me-3"></i>
                    <span>Kelola Layanan</span>
                </a>

                <a href="{{ route('admin.service-categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags me-3"></i>
                    <span>Kategori Layanan</span>
                </a>

                <a href="{{ route('admin.pengumuman.index') }}" class="sidebar-link {{ request()->routeIs('admin.pengumuman.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn me-3"></i>
                    <span>Kelola Pengumuman</span>
                </a>

                @if($user->hasRole('super_admin'))
                    <a href="{{ route('admin.security.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt me-3"></i>
                        <span>Keamanan</span>
                    </a>
                @endif
            @endif

            <!-- FRONT DESK MENU (Petugas Loket) -->
            @if($user->hasRole('petugas-loket'))
                <div class="sidebar-divider">
                    <span>LOKET</span>
                </div>

                <a href="{{ route('frontdesk.triage') }}" class="sidebar-link {{ request()->routeIs('frontdesk.triage') ? 'active' : '' }}">
                    <i class="fas fa-user-check me-3"></i>
                    <span>Triage & Buku Tamu</span>
                </a>

                <a href="{{ route('frontdesk.active-visitors') }}" class="sidebar-link {{ request()->routeIs('frontdesk.active-visitors') ? 'active' : '' }}">
                    <i class="fas fa-users me-3"></i>
                    <span>Tamu Aktif</span>
                </a>

                <a href="{{ route('frontdesk.service.application') }}" class="sidebar-link {{ request()->routeIs('frontdesk.service.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt me-3"></i>
                    <span>Registrasi Layanan</span>
                </a>
            @endif

            <!-- BACK OFFICE MENU (Petugas TU) -->
            @if($user->hasRole('tu'))
                <div class="sidebar-divider">
                    <span>BACK OFFICE</span>
                </div>

                <a href="{{ route('backoffice.tickets.queue') }}" class="sidebar-link {{ request()->routeIs('backoffice.tickets.queue') ? 'active' : '' }}">
                    <i class="fas fa-inbox me-3"></i>
                    <span>Antrian Tugas</span>
                </a>

                <a href="{{ route('backoffice.tickets.my') }}" class="sidebar-link {{ request()->routeIs('backoffice.tickets.my') ? 'active' : '' }}">
                    <i class="fas fa-tasks me-3"></i>
                    <span>Tugas Saya</span>
                </a>

                <a href="{{ route('backoffice.tickets.all') }}" class="sidebar-link {{ request()->routeIs('backoffice.tickets.all') ? 'active' : '' }}">
                    <i class="fas fa-list me-3"></i>
                    <span>Semua Tiket</span>
                </a>
            @endif

            <!-- APPROVAL MENU (Kepala Sekolah, Waka, Kepala TU) -->
            @if($user->hasAnyRole(['kepala-sekolah', 'waka-kesiswaan', 'waka-kurikulum', 'waka-sarpras', 'waka-humas', 'kepala-tu']))
                <div class="sidebar-divider">
                    <span>PERSETUJUAN</span>
                </div>

                <a href="{{ route('backoffice.tickets.queue') }}" class="sidebar-link {{ request()->routeIs('backoffice.tickets.queue') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check me-3"></i>
                    <span>Pending Approval</span>
                </a>

                <a href="{{ route('backoffice.tickets.all') }}" class="sidebar-link {{ request()->routeIs('backoffice.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-history me-3"></i>
                    <span>Riwayat Persetujuan</span>
                </a>
            @endif

            <!-- PUBLIC USER MENU (Guru, Pegawai, Siswa, Wali Murid, Alumni, Instansi, Umum) -->
            @if($user->hasRole(['guru', 'pegawai', 'siswa', 'walimurid', 'alumni', 'instansi', 'umum']) ||
                !$user->hasAnyRole(['super-admin', 'admin', 'petugas-loket', 'tu', 'kepala-sekolah', 'waka-kesiswaan', 'waka-kurikulum', 'waka-sarpras', 'waka-humas', 'kepala-tu']))
                <div class="sidebar-divider">
                    <span>LAYANAN</span>
                </div>

                <a href="{{ route('onlineportal.service.catalog') }}" class="sidebar-link {{ request()->routeIs('onlineportal.service.*') ? 'active' : '' }}">
                    <i class="fas fa-concierge-bell me-3"></i>
                    <span>Katalog Layanan</span>
                </a>

                <a href="{{ route('onlineportal.my-tickets') }}" class="sidebar-link {{ request()->routeIs('onlineportal.my-tickets') || request()->routeIs('onlineportal.ticket.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt me-3"></i>
                    <span>Tiket Saya</span>
                </a>

                <a href="{{ route('onlineportal.track.ticket.form') }}" class="sidebar-link {{ request()->routeIs('onlineportal.track.*') ? 'active' : '' }}">
                    <i class="fas fa-search me-3"></i>
                    <span>Lacak Tiket</span>
                </a>
            @endif

            <!-- COMMON MENU (All Authenticated Users) -->
            <div class="sidebar-divider">
                <span>UMUM</span>
            </div>

            <a href="{{ route('pengumuman.index') }}" class="sidebar-link {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn me-3"></i>
                <span>Pengumuman</span>
            </a>

            <a href="{{ route('supervision.complaints.dashboard') }}" class="sidebar-link {{ request()->routeIs('supervision.complaints.*') || request()->routeIs('supervision.complaint.*') ? 'active' : '' }}">
                <i class="fas fa-comments me-3"></i>
                <span>Pengaduan</span>
            </a>

            <a href="{{ route('supervision.whistleblowing.form') }}" class="sidebar-link {{ request()->routeIs('supervision.whistleblowing.*') ? 'active' : '' }}">
                <i class="fas fa-user-secret me-3"></i>
                <span>Whistleblowing</span>
            </a>

            <a href="{{ route('supervision.skm.survey') }}" class="sidebar-link {{ request()->routeIs('supervision.skm.*') ? 'active' : '' }}">
                <i class="fas fa-poll me-3"></i>
                <span>Survei Kepuasan</span>
            </a>

            @if($user->hasRole(['super-admin', 'admin', 'kepala-sekolah', 'kepala-tu']))
                <div class="sidebar-divider">
                    <span>LAPORAN</span>
                </div>

                <a href="{{ route('backoffice.reports') }}" class="sidebar-link {{ request()->routeIs('backoffice.reports') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span>Laporan Kinerja</span>
                </a>

                <a href="{{ route('supervision.management') }}" class="sidebar-link {{ request()->routeIs('supervision.*') && !request()->routeIs('supervision.complaints.*') && !request()->routeIs('supervision.whistleblowing.*') && !request()->routeIs('supervision.skm.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie me-3"></i>
                    <span>Dashboard SKM/SPAK</span>
                </a>
            @endif

            <!-- SUPER ADMIN MENU -->
            @if($user->hasRole('super_admin'))
                <div class="sidebar-divider">
                    <span>SUPER ADMIN</span>
                </div>

                <a href="{{ route('suadmin.services.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-concierge-bell me-3"></i>
                    <span>Kelola Layanan</span>
                </a>

                <a href="{{ route('suadmin.service-categories.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.service-categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags me-3"></i>
                    <span>Kategori Layanan</span>
                </a>

                <a href="{{ route('suadmin.pengumuman.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.pengumuman.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn me-3"></i>
                    <span>Kelola Pengumuman</span>
                </a>

                <a href="{{ route('suadmin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt me-3"></i>
                    <span>Manajemen Tiket</span>
                </a>

                <a href="{{ route('suadmin.visitors.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.visitors.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-3"></i>
                    <span>Pengunjung</span>
                </a>

                <a href="{{ route('suadmin.complaints.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.complaints.*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-circle me-3"></i>
                    <span>Pengaduan Masyarakat</span>
                </a>

                <a href="{{ route('suadmin.whistleblowing.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.whistleblowing.*') ? 'active' : '' }}">
                    <i class="fas fa-user-secret me-3"></i>
                    <span>Whistleblowing</span>
                </a>

                <div class="sidebar-divider">
                    <span>EVALUASI</span>
                </div>

                <a href="{{ route('suadmin.survey.management') }}" class="sidebar-link {{ request()->routeIs('suadmin.survey.management') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list me-3"></i>
                    <span>Manajemen Survey</span>
                </a>

                <a href="{{ route('suadmin.skm.report') }}" class="sidebar-link {{ request()->routeIs('suadmin.skm.report') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span>Laporan SKM</span>
                </a>

                <a href="{{ route('suadmin.spak.report') }}" class="sidebar-link {{ request()->routeIs('suadmin.spak.report') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt me-3"></i>
                    <span>Laporan SPAK</span>
                </a>

                <a href="{{ route('suadmin.performance.report') }}" class="sidebar-link {{ request()->routeIs('suadmin.performance.report') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie me-3"></i>
                    <span>Laporan Kinerja</span>
                </a>

                <div class="sidebar-divider">
                    <span>KEAMANAN</span>
                </div>

                <a href="{{ route('suadmin.security.dashboard') }}" class="sidebar-link {{ request()->routeIs('suadmin.security.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-lock me-3"></i>
                    <span>Dashboard Keamanan</span>
                </a>

                <a href="{{ route('suadmin.settings.index') }}" class="sidebar-link {{ request()->routeIs('suadmin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog me-3"></i>
                    <span>Konfigurasi</span>
                </a>
            @endif

            <!-- Profile & Settings -->
            <div class="sidebar-divider"></div>

            <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog me-3"></i>
                <span>Profil Saya</span>
            </a>

            <a href="{{ route('home') }}" class="sidebar-link">
                <i class="fas fa-home me-3"></i>
                <span>Kembali ke Beranda</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="mt-3">
                @csrf
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="sidebar-link text-danger w-100 border-0 bg-transparent text-start">
                    <i class="fas fa-sign-out-alt me-3"></i>
                    <span>Keluar</span>
                </a>
            </form>
        </nav>
    </aside>

    <!-- Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
    /* Toggle Button (Mobile) - Icon only */
    .sidebar-toggle {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1050;
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 12px;
        background: var(--bs-primary);
        color: white;
        font-size: 1.2rem;
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .sidebar-toggle:hover {
        transform: scale(1.05);
        box-shadow: var(--shadow-xl);
    }

    @media (min-width: 992px) {
        .sidebar-toggle {
            display: none;
        }
    }

    /* Sidebar - Fixed Position (now positioned below topbar) */
    .sidebar {
        position: fixed;
        top: var(--topbar-height); /* Positioned below the topbar */
        left: 0;
        bottom: 0;
        width: 240px;
        background: linear-gradient(180deg, #f8faf9 0%, #f0f4f2 100%);
        border-right: 2px solid rgba(20, 83, 45, 0.1);
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 1030;
        transition: transform 0.3s ease;
        padding: 1rem 0;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
        min-height: calc(100vh - var(--topbar-height)); /* Adjusted for topbar height */
    }

    /* Slightly wider for very large screens only */
    @media (min-width: 1600px) {
        .sidebar {
            width: 260px;
        }
    }

    [data-theme="dark"] .sidebar {
        background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
        border-right: 2px solid rgba(20, 83, 45, 0.3);
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
    }

    /* For desktop, sidebar has fixed width */
    @media (min-width: 992px) {
        .sidebar {
            width: 240px;
            flex-shrink: 0;
        }
    }
    
    /* Mobile: Hidden by default */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
            top: 0; /* On mobile, start from top when shown */
            height: 100vh;
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }

    /* Sidebar Header */
    .sidebar-header {
        padding: 0 1rem 1rem;
        border-bottom: 1px solid var(--bs-border);
        margin-bottom: 0.75rem;
    }

    .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Sidebar Navigation */
    .sidebar-nav {
        padding: 0 0.75rem;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.625rem 0.75rem;
        color: var(--bs-text);
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .sidebar-link:hover {
        background: rgba(20, 83, 45, 0.05);
        color: var(--bs-primary);
        transform: translateX(4px);
    }

    [data-theme="dark"] .sidebar-link:hover {
        background: rgba(20, 83, 45, 0.2);
    }

    .sidebar-link.active {
        background: rgba(20, 83, 45, 0.1);
        color: var(--bs-primary);
        font-weight: 600;
    }

    [data-theme="dark"] .sidebar-link.active {
        background: rgba(20, 83, 45, 0.3);
    }

    .sidebar-link i {
        width: 18px;
        text-align: center;
        font-size: 0.875rem;
    }

    /* Sidebar Navigation */
    .sidebar-nav {
        padding: 0 0.75rem;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.625rem 0.75rem;
        color: var(--bs-text);
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .sidebar-link:hover {
        background: rgba(20, 83, 45, 0.05);
        color: var(--bs-primary);
        transform: translateX(4px);
    }

    [data-theme="dark"] .sidebar-link:hover {
        background: rgba(20, 83, 45, 0.2);
    }

    .sidebar-link.active {
        background: rgba(20, 83, 45, 0.1);
        color: var(--bs-primary);
        font-weight: 600;
    }

    [data-theme="dark"] .sidebar-link.active {
        background: rgba(20, 83, 45, 0.3);
    }

    .sidebar-link i {
        width: 18px;
        text-align: center;
        font-size: 0.875rem;
    }

    /* Sidebar Divider */
    .sidebar-divider {
        margin: 0.75rem 0 0.5rem 0.75rem;
        padding-bottom: 0.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--bs-gray-500);
    }

    .sidebar-divider:first-child {
        margin-top: 0;
    }

    /* Sidebar Overlay (Mobile) */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040; /* Higher than sidebar z-index */
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .sidebar-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    @media (min-width: 992px) {
        .sidebar-overlay {
            display: none;
        }
    }

    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--bs-gray-400);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--bs-gray-500);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Toggle Sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    });
});
</script>
