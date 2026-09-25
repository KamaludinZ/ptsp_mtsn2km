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
                $homeUrl = get_dashboard_route_for_user($user);
                $isLeader = $user->hasAnyRole(\App\Support\RoleAccess::LEADERSHIP);
                $pendingApprovals = $isLeader ? \App\Models\Ticket::approvableBy($user)->count() : 0;
                $link = fn (string $pattern) => request()->routeIs($pattern) ? 'active' : '';
            @endphp

            <!-- Dashboard utama sesuai peran -->
            <a href="{{ $homeUrl }}" class="sidebar-link {{ request()->is(ltrim($homeUrl, '/')) ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt me-3" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>

            <!-- PIMPINAN: dashboard eksekutif (Modul 13) & persetujuan (Modul 8) -->
            @if ($isLeader)
                <div class="sidebar-divider"><span>PIMPINAN</span></div>

                @if ($homeUrl !== '/pimpinan')
                    <a href="{{ route('leadership.dashboard') }}" class="sidebar-link {{ $link('leadership.dashboard') }}">
                        <i class="fas fa-chart-line me-3" aria-hidden="true"></i>
                        <span>Dashboard Eksekutif</span>
                    </a>
                @endif

                <a href="{{ route('leadership.approvals') }}" class="sidebar-link {{ $link('leadership.approvals') }}">
                    <i class="fas fa-clipboard-check me-3" aria-hidden="true"></i>
                    <span>Persetujuan</span>
                    @if ($pendingApprovals)
                        <span class="badge rounded-pill bg-warning text-dark ms-auto">{{ $pendingApprovals }}<span class="visually-hidden"> menunggu</span></span>
                    @endif
                </a>
            @endif

            <!-- LOKET (Modul 1-2) -->
            @can('frontdesk.access')
                <div class="sidebar-divider"><span>LOKET</span></div>

                @if ($homeUrl !== '/frontdesk/dashboard')
                    <a href="{{ route('frontdesk.dashboard') }}" class="sidebar-link {{ $link('frontdesk.dashboard') }}">
                        <i class="fas fa-desktop me-3" aria-hidden="true"></i>
                        <span>Dashboard Loket</span>
                    </a>
                @endif

                <a href="{{ route('frontdesk.triage') }}" class="sidebar-link {{ $link('frontdesk.triage') }}">
                    <i class="fas fa-user-check me-3" aria-hidden="true"></i>
                    <span>Triage Pengunjung</span>
                </a>

                <a href="{{ route('frontdesk.service.application') }}" class="sidebar-link {{ $link('frontdesk.service.*') }}">
                    <i class="fas fa-file-circle-plus me-3" aria-hidden="true"></i>
                    <span>Registrasi Layanan</span>
                </a>

                <a href="{{ route('frontdesk.visitor-book') }}" class="sidebar-link {{ $link('frontdesk.visitor-book') }}">
                    <i class="fas fa-book me-3" aria-hidden="true"></i>
                    <span>Buku Tamu</span>
                </a>

                <a href="{{ route('frontdesk.active-visitors') }}" class="sidebar-link {{ $link('frontdesk.active-visitors') }}">
                    <i class="fas fa-users me-3" aria-hidden="true"></i>
                    <span>Tamu Aktif</span>
                </a>
            @endcan

            <!-- BACK OFFICE (Modul 7 & 9) -->
            @can('backoffice.access')
                <div class="sidebar-divider"><span>BACK OFFICE</span></div>

                @if ($homeUrl !== '/backoffice/dashboard')
                    <a href="{{ route('backoffice.dashboard') }}" class="sidebar-link {{ $link('backoffice.dashboard') }}">
                        <i class="fas fa-briefcase me-3" aria-hidden="true"></i>
                        <span>Dashboard Back Office</span>
                    </a>
                @endif

                <a href="{{ route('backoffice.tickets.queue') }}" class="sidebar-link {{ $link('backoffice.tickets.queue') }}">
                    <i class="fas fa-inbox me-3" aria-hidden="true"></i>
                    <span>Antrian Tugas</span>
                </a>

                <a href="{{ route('backoffice.tickets.my') }}" class="sidebar-link {{ $link('backoffice.tickets.my') }}">
                    <i class="fas fa-tasks me-3" aria-hidden="true"></i>
                    <span>Tugas Saya</span>
                </a>

                <a href="{{ route('backoffice.tickets.all') }}" class="sidebar-link {{ $link('backoffice.tickets.all') }}">
                    <i class="fas fa-list me-3" aria-hidden="true"></i>
                    <span>Semua Tiket</span>
                </a>

                <a href="{{ route('backoffice.tickets.search') }}" class="sidebar-link {{ $link('backoffice.tickets.search') }}">
                    <i class="fas fa-magnifying-glass me-3" aria-hidden="true"></i>
                    <span>Cari Tiket</span>
                </a>

                <a href="{{ route('backoffice.reports') }}" class="sidebar-link {{ $link('backoffice.reports') }}">
                    <i class="fas fa-chart-bar me-3" aria-hidden="true"></i>
                    <span>Laporan Layanan</span>
                </a>
            @endcan

            <!-- PENGAWASAN (Modul 10-11) -->
            @if ($user->can('supervision.access') || $user->hasAnyRole(\App\Support\RoleAccess::COMPLAINT_HANDLERS))
                <div class="sidebar-divider"><span>PENGAWASAN</span></div>

                @can('supervision.access')
                    @if ($homeUrl !== '/supervision/management')
                        <a href="{{ route('supervision.management') }}" class="sidebar-link {{ $link('supervision.management') }}">
                            <i class="fas fa-binoculars me-3" aria-hidden="true"></i>
                            <span>Dashboard Pengawasan</span>
                        </a>
                    @endif

                    <a href="{{ route('supervision.performance') }}" class="sidebar-link {{ $link('supervision.performance') }}">
                        <i class="fas fa-gauge-high me-3" aria-hidden="true"></i>
                        <span>Kinerja Pelayanan</span>
                    </a>
                @endcan

                @if ($user->hasAnyRole(\App\Support\RoleAccess::COMPLAINT_HANDLERS))
                    <a href="{{ route('admin.complaints.index') }}" class="sidebar-link {{ $link('admin.complaints.*') }}">
                        <i class="fas fa-comments me-3" aria-hidden="true"></i>
                        <span>Tindak Lanjut Pengaduan</span>
                    </a>

                    <a href="{{ route('admin.whistleblowing.index') }}" class="sidebar-link {{ $link('admin.whistleblowing.*') }}">
                        <i class="fas fa-user-secret me-3" aria-hidden="true"></i>
                        <span>Whistleblowing</span>
                    </a>

                    <a href="{{ route('admin.performance.report') }}" class="sidebar-link {{ request()->routeIs('admin.performance.report', 'admin.skm.report', 'admin.spak.report') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie me-3" aria-hidden="true"></i>
                        <span>Laporan SKM &amp; SPAK</span>
                    </a>
                @endif
            @endif

            <!-- ADMIN (Modul 12) -->
            @if ($user->hasRole('admin'))
                <div class="sidebar-divider"><span>ADMIN</span></div>

                <a href="{{ route('admin.services.index') }}" class="sidebar-link {{ $link('admin.services.*') }}">
                    <i class="fas fa-concierge-bell me-3" aria-hidden="true"></i>
                    <span>Kelola Layanan</span>
                </a>

                <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ $link('admin.tickets.*') }}">
                    <i class="fas fa-ticket me-3" aria-hidden="true"></i>
                    <span>Kelola Tiket</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ $link('admin.users.*') }}">
                    <i class="fas fa-users-cog me-3" aria-hidden="true"></i>
                    <span>Pengguna</span>
                </a>

                <a href="{{ route('admin.survey.management') }}" class="sidebar-link {{ $link('admin.survey.*') }}">
                    <i class="fas fa-poll me-3" aria-hidden="true"></i>
                    <span>Kelola Survei</span>
                </a>

                <a href="{{ route('admin.pengumuman.index') }}" class="sidebar-link {{ $link('admin.pengumuman.*') }}">
                    <i class="fas fa-bullhorn me-3" aria-hidden="true"></i>
                    <span>Kelola Pengumuman</span>
                </a>

                <a href="{{ route('admin.security.dashboard') }}" class="sidebar-link {{ $link('admin.security.*') }}">
                    <i class="fas fa-shield-alt me-3" aria-hidden="true"></i>
                    <span>Keamanan</span>
                </a>
            @endif

            @if ($user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
                <a href="{{ url('/cp') }}" class="sidebar-link">
                    <i class="fas fa-sliders me-3" aria-hidden="true"></i>
                    <span>Panel Kontrol</span>
                </a>
            @endif

            <!-- LAYANAN: pemohon (guru, pegawai, siswa, wali murid, alumni, instansi, umum) -->
            @if (! $user->isStaff())
                <div class="sidebar-divider"><span>LAYANAN</span></div>

                <a href="{{ route('onlineportal.service.catalog') }}" class="sidebar-link {{ $link('onlineportal.service.*') }}">
                    <i class="fas fa-concierge-bell me-3" aria-hidden="true"></i>
                    <span>Katalog Layanan</span>
                </a>

                <a href="{{ route('onlineportal.my-tickets') }}" class="sidebar-link {{ request()->routeIs('onlineportal.my-tickets', 'onlineportal.ticket.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt me-3" aria-hidden="true"></i>
                    <span>Tiket Saya</span>
                </a>

                <a href="{{ route('onlineportal.track.ticket.form') }}" class="sidebar-link {{ $link('onlineportal.track.*') }}">
                    <i class="fas fa-search me-3" aria-hidden="true"></i>
                    <span>Lacak Tiket</span>
                </a>

                <a href="{{ route('survey.form') }}" class="sidebar-link {{ $link('survey.*') }}">
                    <i class="fas fa-star-half-stroke me-3" aria-hidden="true"></i>
                    <span>Survei Kepuasan</span>
                </a>

                <a href="{{ route('supervision.complaint.submit') }}" class="sidebar-link {{ $link('supervision.complaint.*') }}">
                    <i class="fas fa-comments me-3" aria-hidden="true"></i>
                    <span>Pengaduan &amp; Saran</span>
                </a>

                <a href="{{ route('supervision.whistleblowing.form') }}" class="sidebar-link {{ $link('supervision.whistleblowing.*') }}">
                    <i class="fas fa-user-secret me-3" aria-hidden="true"></i>
                    <span>Whistleblowing</span>
                </a>
            @endif

            <!-- UMUM -->
            <div class="sidebar-divider"><span>UMUM</span></div>

            <a href="{{ route('pengumuman.index') }}" class="sidebar-link {{ $link('pengumuman.*') }}">
                <i class="fas fa-bullhorn me-3" aria-hidden="true"></i>
                <span>Pengumuman</span>
            </a>

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
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <button class="sidebar-collapse-toggle" id="sidebarCollapseToggle" aria-label="Toggle Sidebar Collapse">
                <i class="fas fa-angle-double-left"></i>
            </button>
        </div>
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
        transition: width 0.3s ease, transform 0.3s ease;
        padding: 1rem 0;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
        min-height: calc(100vh - var(--topbar-height)); /* Adjusted for topbar height */
        display: flex;
        flex-direction: column;
    }

    .main-panel {
        transition: margin-left 0.3s ease;
    }
    
    .sidebar-nav {
        flex-grow: 1;
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
        .main-panel {
            margin-left: 240px;
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
    
    .sidebar.collapsed .sidebar-header .flex-grow-1 {
        display: none;
    }
    
    .sidebar.collapsed .sidebar-header .avatar-circle {
        margin-right: 0 !important;
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
        white-space: nowrap;
    }

    .sidebar-link:hover {
        background: rgba(20, 83, 45, 0.05);
        color: var(--bs-primary);
        transform: translateX(4px);
    }
    
    .sidebar.collapsed .sidebar-link:hover {
        transform: translateX(0);
        background: rgba(20, 83, 45, 0.1);
    }
    
    .sidebar.collapsed .sidebar-link {
        justify-content: center;
    }
    
    .sidebar.collapsed .sidebar-link span {
        display: none;
    }

    .sidebar.collapsed .sidebar-link i {
        margin-right: 0 !important;
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
        white-space: nowrap;
    }
    
    .sidebar.collapsed .sidebar-divider {
        font-size: 0;
    }
    
    .sidebar.collapsed .sidebar-divider::before {
        content: '...';
        font-size: 1rem;
        display: block;
        text-align: center;
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
    
    /* Collapsed state */
    @media (min-width: 992px) {
        .sidebar.collapsed {
            width: 80px;
        }

        .main-panel.collapsed {
            margin-left: 80px;
        }
    }
    
    /* Sidebar Footer for Collapse Button */
    .sidebar-footer {
        padding: 0.5rem 0.75rem;
        border-top: 1px solid var(--bs-border);
        text-align: center;
    }

    .sidebar-collapse-toggle {
        background: none;
        border: none;
        color: var(--bs-gray-600);
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .sidebar.collapsed .sidebar-collapse-toggle i {
        transform: rotate(180deg);
    }

    .sidebar-collapse-toggle:hover {
        color: var(--bs-primary);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarCollapseToggle = document.getElementById('sidebarCollapseToggle');
    const mainPanel = document.querySelector('.main-panel');
    const SIDEBAR_COLLAPSED_KEY = 'sidebar_collapsed';

    // Apply saved state for collapsed sidebar
    if (localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === 'true') {
        sidebar.classList.add('collapsed');
        if(mainPanel) mainPanel.classList.add('collapsed');
    }

    // Toggle Sidebar (Mobile)
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
    
    // Toggle Sidebar Collapse (Desktop)
    if (sidebarCollapseToggle) {
        sidebarCollapseToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            if(mainPanel) mainPanel.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem(SIDEBAR_COLLAPSED_KEY, isCollapsed);
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
