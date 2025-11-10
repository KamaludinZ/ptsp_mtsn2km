<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PTSP MTsN 2 KOTA MALANG - Super Admin')</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Vite Assets (includes Bootstrap Icons locally) -->
    @vite(['resources/css/app.css', 'resources/css/bootstrap-custom.css', 'resources/js/bootstrap-bundle.js', 'resources/js/chart-bundle.js', 'resources/js/app.js'])
    
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
            --bs-gray-400: #9ca3af;
            --bs-gray-500: #6b7280;
            --bs-gray-600: #4b5563;
            --bs-gray-700: #374151;
            --bs-gray-800: #1f2937;
            --bs-gray-900: #111827;

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

            --sidebar-width: 260px;
            --sidebar-width-collapsed: 80px;
            --topbar-height: 60px;
        }

        /* Dark theme with adaptive color system */
        [data-theme="dark"] {
            /* Dark theme colors */
            --bs-primary: #14532d;
            --bs-primary-dark: #052e16;
            --bs-primary-light: #166534;
            --bs-primary-rgb: 20, 83, 45;

            --bs-secondary: #ea580c;
            --bs-secondary-dark: #c2410c;
            --bs-secondary-light: #f97316;
            --bs-secondary-rgb: 234, 88, 12;

            --bs-white: #1f2937; /* Adjusted for dark mode */
            --bs-gray-50: #374151;
            --bs-gray-100: #4b5563;
            --bs-gray-200: #6b7280;
            --bs-gray-300: #9ca3af;
            --bs-gray-400: #d1d5db;
            --bs-gray-500: #e5e7eb;
            --bs-gray-600: #f3f4f6;
            --bs-gray-700: #f9fafb;
            --bs-gray-800: #ffffff;
            --bs-gray-900: #ffffff;

            /* Semantic colors (can be adjusted for dark mode if needed) */
            --bs-success: #10b981;
            --bs-warning: #f59e0b;
            --bs-danger: #ef4444;
            --bs-info: #3b82f6;

            /* Accessibility colors (can be adjusted for dark mode if needed) */
            --bs-focus: #60a5fa;
            --bs-text: #f9fafb; /* Light text on dark background */
            --bs-bg: #111827; /* Dark background */
            --bs-surface: #1f2937; /* Darker surface for cards/panels */
            --bs-border: #374151;

            /* ADAPTIVE COLOR SYSTEM - Define specific colors for components within dark mode */
            --theme-primary-bg: #052e16;     /* Primary dark background */
            --theme-primary-text: #86efac;   /* Primary light text */
            --theme-secondary-bg: #7c2d12;   /* Secondary dark background */
            --theme-secondary-text: #fdba74; /* Secondary light text */
        }

        body {
            font-family: var(--bs-font-sans);
            background-color: var(--bs-bg);
            color: var(--bs-text);
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        .dashboard-layout {
            display: flex;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--bs-white);
            border-right: 1px solid var(--bs-border);
            transition: width 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
            z-index: 1100;
            overflow-x: hidden;
            flex-shrink: 0; /* Prevent sidebar from shrinking */
        }

        /* Collapsed Sidebar styles */
        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar.collapsed .sidebar-content .p-3,
        .sidebar.collapsed .sidebar-content .p-4 {
            display: none; /* Hide section titles */
        }

        .sidebar.collapsed .nav-link span {
            display: none; /* Hide link text */
        }

        .sidebar.collapsed .nav-link {
            justify-content: center; /* Center icons */
            padding: 0.75rem 0; /* Adjust padding for icon-only */
        }

        .main-panel {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
            width: calc(100% - var(--sidebar-width));
        }

        .main-panel.collapsed {
            margin-left: var(--sidebar-width-collapsed);
            width: calc(100% - var(--sidebar-width-collapsed));
        }

        .topbar {
            height: var(--topbar-height);
            background-color: var(--bs-white);
            border-bottom: 1px solid var(--bs-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .content-wrapper {
            flex-grow: 1;
            padding: 2rem;
            background-color: var(--bs-surface);
            overflow-y: auto;
            transition: background-color 0.3s ease;
        }

        .footer {
            padding: 1rem 1.5rem;
            background-color: var(--bs-white);
            border-top: 1px solid var(--bs-border);
            font-size: 0.875rem;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .nav-link:hover {
            background-color: var(--bs-gray-100);
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: -100%; /* Hide sidebar off-screen */
                z-index: 1200;
                width: 250px; /* Full width for mobile expanded */
                box-shadow: var(--shadow-lg);
            }

            .sidebar.expanded {
                left: 0; /* Slide in */
            }

            .main-panel {
                margin-left: 0;
                width: 100%;
            }

            .main-panel.collapsed {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1150;
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Card styles for admin panel */
        .card {
            background-color: var(--bs-white);
            border: 1px solid var(--bs-border);
            border-radius: 0.75rem; /* Rounded corners for cards */
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .card-header {
            background-color: var(--bs-gray-50);
            border-bottom: 1px solid var(--bs-border);
            color: var(--bs-text);
            padding: 1rem 1.5rem;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--bs-primary-dark);
            border-color: var(--bs-primary-dark);
        }

        .btn-outline-secondary {
            color: var(--bs-gray-600);
            border-color: var(--bs-gray-300);
        }

        .btn-outline-secondary:hover {
            color: var(--bs-text);
            background-color: var(--bs-gray-100);
            border-color: var(--bs-gray-400);
        }

        .text-amber-600 {
            color: var(--bs-secondary) !important;
        }

        .bg-amber-600 {
            background-color: var(--bs-secondary) !important;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 0.4em 0.7em;
            border-radius: 0.375rem;
        }

        /* Badge Color Definitions */
        .bg-success {
            background-color: var(--bs-success) !important;
            color: #fff !important;
        }

        .bg-danger {
            background-color: var(--bs-danger) !important;
            color: #fff !important;
        }

        .bg-warning {
            background-color: var(--bs-warning) !important;
            color: #000 !important;
        }

        .bg-info {
            background-color: var(--bs-info) !important;
            color: #fff !important;
        }

        .bg-secondary {
            background-color: var(--bs-gray-500) !important;
            color: #fff !important;
        }

        .bg-primary {
            background-color: var(--bs-primary) !important;
            color: #fff !important;
        }

        /* Background Opacity Utilities */
        .bg-primary.bg-opacity-10 {
            background-color: rgba(20, 83, 45, 0.1) !important;
        }

        .bg-success.bg-opacity-10 {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        .bg-warning.bg-opacity-10 {
            background-color: rgba(245, 158, 11, 0.1) !important;
        }

        .bg-info.bg-opacity-10 {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .bg-secondary.bg-opacity-10 {
            background-color: rgba(107, 114, 128, 0.1) !important;
        }

        /* Text color for icons */
        .text-primary {
            color: var(--bs-primary) !important;
        }

        .text-success {
            color: var(--bs-success) !important;
        }

        .text-danger {
            color: var(--bs-danger) !important;
        }

        .text-warning {
            color: var(--bs-warning) !important;
        }

        .text-info {
            color: var(--bs-info) !important;
        }

        /* Text Colors */
        .text-success {
            color: var(--bs-success) !important;
        }

        .text-danger {
            color: var(--bs-danger) !important;
        }

        .text-warning {
            color: var(--bs-warning) !important;
        }

        .text-info {
            color: var(--bs-info) !important;
        }

        .text-secondary {
            color: var(--bs-gray-500) !important;
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            padding: 0.25em 0.6em;
            border-radius: 50%;
            font-size: 0.7em;
            line-height: 1;
            border: 2px solid var(--bs-white); /* White border for contrast */
        }

        /* Dropdowns */
        .dropdown-menu {
            border-radius: 0.5rem;
            border: 1px solid var(--bs-border);
            box-shadow: var(--shadow-md);
            background-color: var(--bs-white);
            transition: all 0.3s ease;
        }

        .dropdown-item {
            color: var(--bs-text);
        }

        .dropdown-item:hover {
            background-color: var(--bs-gray-100);
            color: var(--bs-text);
        }

        .notification-dropdown .dropdown-item:hover div div, .notification-dropdown .dropdown-item:hover small {
            color: var(--bs-text) !important;
        }

        /* Input Group */
        .input-group .form-control,
        .input-group .btn {
            border-color: var(--bs-border);
        }

        .input-group .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }

        /* Table */
        .table {
            --bs-table-bg: var(--bs-white);
            --bs-table-color: var(--bs-text);
            --bs-table-hover-bg: var(--bs-gray-50);
            border-color: var(--bs-border);
        }

        .table-light {
            --bs-table-bg: var(--bs-gray-50);
            --bs-table-color: var(--bs-gray-700);
        }

        [data-theme="dark"] .sidebar {
            background-color: var(--bs-surface);
            border-right-color: var(--bs-border);
        }

        [data-theme="dark"] .topbar {
            background-color: var(--bs-surface);
            border-bottom-color: var(--bs-border);
        }

        [data-theme="dark"] .content-wrapper {
            background-color: var(--bs-bg);
        }

        [data-theme="dark"] .footer {
            background-color: var(--bs-surface);
            border-top-color: var(--bs-border);
        }

        [data-theme="dark"] .nav-link {
            color: var(--bs-gray-300);
        }

        [data-theme="dark"] .nav-link.active {
            background-color: var(--bs-primary) !important;
            color: var(--bs-white) !important;
        }

        [data-theme="dark"] .nav-link:hover {
            background-color: var(--bs-gray-700);
            color: var(--bs-white);
        }

        [data-theme="dark"] .card {
            background-color: var(--bs-surface);
            border-color: var(--bs-border);
        }

        [data-theme="dark"] .card-header {
            background-color: var(--bs-gray-700);
            border-bottom-color: var(--bs-border);
            color: var(--bs-white);
        }

        [data-theme="dark"] .table {
            --bs-table-bg: var(--bs-surface);
            --bs-table-color: var(--bs-text);
            --bs-table-hover-bg: var(--bs-gray-700);
            border-color: var(--bs-border);
        }

        [data-theme="dark"] .table-light {
            --bs-table-bg: var(--bs-gray-700);
            --bs-table-color: var(--bs-gray-100);
        }

        [data-theme="dark"] .btn-outline-secondary {
            color: var(--bs-gray-300);
            border-color: var(--bs-gray-500);
        }

        [data-theme="dark"] .btn-outline-secondary:hover {
            color: var(--bs-white);
            background-color: var(--bs-gray-600);
            border-color: var(--bs-gray-600);
        }

        [data-theme="dark"] .dropdown-menu {
            background-color: var(--bs-surface);
            border-color: var(--bs-border);
        }

        [data-theme="dark"] .dropdown-item {
            color: var(--bs-gray-300);
        }

        [data-theme="dark"] .dropdown-item:hover {
            background-color: var(--bs-gray-700);
            color: var(--bs-white);
        }

        /* Dark Mode Badge Adjustments */
        [data-theme="dark"] .bg-success {
            background-color: #16a34a !important;
        }

        [data-theme="dark"] .bg-danger {
            background-color: #dc2626 !important;
        }

        [data-theme="dark"] .bg-warning {
            background-color: #f59e0b !important;
            color: #000 !important;
        }

        [data-theme="dark"] .bg-info {
            background-color: #3b82f6 !important;
        }

        [data-theme="dark"] .bg-secondary {
            background-color: var(--bs-gray-600) !important;
        }

        [data-theme="dark"] .bg-primary {
            background-color: #166534 !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="dashboard-layout">
        <div class="sidebar-overlay"></div>
        <!-- Fixed Sidebar -->
        <div class="sidebar d-flex flex-column" id="sidebar">
            <div class="sidebar-content">
                <div class="p-3">
                    <h6 class="fw-bold text-uppercase text-amber-600 mb-0 ps-1">Menu</h6>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.dashboard')) active @endif" href="{{ route('suadmin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                <div class="p-3 border-top">
                    <h6 class="fw-bold text-uppercase text-amber-600 mb-0 ps-1">Master Data</h6>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.services.*')) active @endif" href="{{ route('suadmin.services.index') }}">
                            <i class="bi bi-cone-striped me-2"></i>
                            <span>Layanan</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.service-categories.*')) active @endif" href="{{ route('suadmin.service-categories.index') }}">
                            <i class="bi bi-tags me-2"></i>
                            <span>Kategori Layanan</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.pengumuman.*')) active @endif" href="{{ route('suadmin.pengumuman.index') }}">
                            <i class="bi bi-megaphone me-2"></i>
                            <span>Pengumuman</span>
                        </a>
                    </li>
                </ul>

                <div class="p-3 border-top">
                    <h6 class="fw-bold text-uppercase text-amber-600 mb-0 ps-1">Manajemen Tiket</h6>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.tickets.*')) active @endif" href="{{ route('suadmin.tickets.index') }}">
                            <i class="bi bi-ticket-detailed me-2"></i>
                            <span>Tiket Layanan</span>
                        </a>
                    </li>
                </ul>

                <div class="p-3 border-top">
                    <h6 class="fw-bold text-uppercase text-amber-600 mb-0 ps-1">Pengunjung</h6>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.visitors.*')) active @endif" href="{{ route('suadmin.visitors.index') }}">
                            <i class="bi bi-person-walking me-2"></i>
                            <span>Buku Tamu</span>
                        </a>
                    </li>
                </ul>

                <div class="p-3 border-top">
                    <h6 class="fw-bold text-uppercase text-amber-600 mb-0 ps-1">Pengaduan</h6>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.complaints.*') && !request()->routeIs('suadmin.whistleblowing.*')) active @endif" href="{{ route('suadmin.complaints.index') }}">
                            <i class="bi bi-chat-left-text me-2"></i>
                            <span>Pengaduan Masyarakat</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.whistleblowing.*')) active @endif" href="{{ route('suadmin.whistleblowing.index') }}">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            <span>Whistleblowing</span>
                        </a>
                    </li>
                </ul>

                <div class="p-3 border-top">
                    <h6 class="fw-bold text-uppercase text-amber-600 mb-0 ps-1">Pengawasan & Evaluasi</h6>
                </div>
                <ul class="nav flex-column px-3">
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.survey.management')) active @endif" href="{{ route('suadmin.survey.management') }}">
                        <i class="bi bi-clipboard-check me-2"></i>
                        <span>Manajemen Survey</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.skm.*')) active @endif" href="{{ route('suadmin.skm.report') }}">
                        <i class="bi bi-graph-up me-2"></i>
                        <span>Laporan SKM</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.spak.*')) active @endif" href="{{ route('suadmin.spak.report') }}">
                        <i class="bi bi-shield-check me-2"></i>
                        <span>Laporan SPAK</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.performance.*')) active @endif" href="{{ route('suadmin.performance.report') }}">
                        <i class="bi bi-bar-chart-line me-2"></i>
                        <span>Laporan Kinerja</span>
                    </a>
                </li>
            </ul>

            <div class="p-4 border-top">
                <h6 class="fw-bold text-uppercase text-amber-600 mb-0">Keamanan</h6>
            </div>
            <ul class="nav flex-column px-3">
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.security.*')) active @endif" href="{{ route('suadmin.security.dashboard') }}">
                        <i class="bi bi-shield-lock me-2"></i>
                        <span>Security Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="p-4 border-top">
                <h6 class="fw-bold text-uppercase text-amber-600 mb-0">Konfigurasi</h6>
            </div>
            <ul class="nav flex-column px-3">
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center @if(request()->routeIs('suadmin.settings.*')) active @endif" href="{{ route('suadmin.settings.index') }}">
                        <i class="bi bi-gear me-2"></i>
                        <span>Pengaturan Umum</span>
                    </a>
                </li>
            </ul>
            <div class="p-3 border-top sidebar-content">
                <small class="text-muted">&copy; 2025 PTSP MTsN 2 KOTA MALANG</small>
            </div>
        </div>
        </div>

        <div class="main-panel">
            <nav class="topbar d-flex align-items-center justify-content-between" id="topbar">
                <div class="d-flex align-items-center">
                    <!-- Sidebar Toggle Button -->
                    <button id="sidebarToggle" class="btn me-3" aria-label="Toggle menu">
                        <i class="bi bi-list fs-4" aria-hidden="true"></i>
                    </button>
                    
                    <!-- Brand -->
                    <a href="{{ route('suadmin.dashboard') }}" class="text-decoration-none">
                        <h4 class="mb-0 fw-bold text-amber-600">PTSP MTsN 2 KOTA MALANG</h4>
                    </a>
                </div>

                <div class="d-flex align-items-center">
                    <!-- Notification Bell -->
                    <div class="dropdown me-3">
                        <a href="#" class="btn position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            @php
                                $pendingTicketsCount = \App\Models\Ticket::where('status', 'pending')->count();
                                $pendingApprovalsCount = \App\Models\Ticket::where('status', 'pending_approval')->count();
                                $complaintsCount = \App\Models\Complaint::where('status', 'pending')->count();
                                $totalUnreadNotifications = $pendingTicketsCount + $pendingApprovalsCount + $complaintsCount;
                            @endphp
                            @if($totalUnreadNotifications > 0)
                                <span class="notification-badge bg-danger text-white">{{ $totalUnreadNotifications }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                            @if($pendingTicketsCount > 0)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('suadmin.tickets.index') }}?status=pending">
                                        <div class="me-3 p-2 rounded bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-ticket-perforated"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $pendingTicketsCount }} Tiket Baru</div>
                                            <small class="text-muted">Menunggu verifikasi</small>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if($pendingApprovalsCount > 0)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('suadmin.tickets.index') }}?status=pending_approval">
                                        <div class="me-3 p-2 rounded bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $pendingApprovalsCount }} Persetujuan Menunggu</div>
                                            <small class="text-muted">Menunggu approval</small>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if($complaintsCount > 0)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('suadmin.complaints.index') }}?status=pending">
                                        <div class="me-3 p-2 rounded bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $complaintsCount }} Pengaduan Masuk</div>
                                            <small class="text-muted">Menunggu ditanggapi</small>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if($totalUnreadNotifications === 0)
                                <li><span class="dropdown-item text-center text-muted">Tidak ada notifikasi baru</span></li>
                            @endif
                        </ul>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar && str_contains(auth()->user()->avatar, 'ui-avatars.com') ? auth()->user()->avatar : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=amber' }}" 
                                 class="rounded-circle me-2" width="40" height="40">
                            <div class="d-none d-md-block">
                                <div class="fw-medium">{{ auth()->user()->name }}</div>
                                <small class="text-muted">Super Admin</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item-text text-muted small">Tema</span></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="light"><i class="bi bi-sun me-2"></i> Terang</a></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="dark"><i class="bi bi-moon me-2"></i> Gelap</a></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="auto"><i class="bi bi-laptop me-2"></i> Sistem</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-key me-2"></i> Ubah Kata Sandi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Content Wrapper -->
            <main class="content-wrapper">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-12 text-center">
                            <p class="mb-0">v1.0.0 © 2025 PTSP MTsN 2 KOTA MALANG. Hak Cipta Dilindungi. | Dikembangkan dengan ❤️ oleh Tim PUSKOM</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainPanel = document.querySelector('.main-panel');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');

            const isDesktop = () => window.innerWidth > 992;

            // Function to toggle sidebar
            const toggleSidebar = () => {
                if (isDesktop()) {
                    sidebar.classList.toggle('collapsed');
                    mainPanel.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                } else {
                    sidebar.classList.toggle('expanded'); // This is for mobile overlay
                    sidebarOverlay.classList.toggle('active');
                }
            };


            // Event listener for the toggle button
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }

            // Event listener for the overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.remove('expanded');
                    sidebarOverlay.classList.remove('active');
                });
            }

            // Initial state for desktop
            if (isDesktop()) {
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    sidebar.classList.add('collapsed');
                    mainPanel.classList.add('collapsed');
                }
            }

            // Adjust on resize
            window.addEventListener('resize', () => {
                if (isDesktop()) {
                    sidebar.classList.remove('expanded');
                    sidebarOverlay.style.display = 'none';
                } else {
                    sidebar.classList.remove('collapsed');
                    mainPanel.classList.remove('collapsed');
                }
            });
        });

        // Theme Switcher Implementation
        const themeSelectors = document.querySelectorAll('.theme-selector');
        const htmlElement = document.documentElement;

        // Function to set theme
        const setTheme = (theme) => {
            if (theme === 'auto') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                htmlElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            } else {
                htmlElement.setAttribute('data-theme', theme);
            }
            localStorage.setItem('theme', theme);

            // Update active state
            themeSelectors.forEach(selector => {
                selector.classList.remove('active');
                if (selector.getAttribute('data-theme') === theme) {
                    selector.classList.add('active');
                }
            });
        };

        // Get saved theme or default to auto
        const savedTheme = localStorage.getItem('theme') || 'auto';
        setTheme(savedTheme);

        // Theme selector click handlers
        themeSelectors.forEach(selector => {
            selector.addEventListener('click', (e) => {
                e.preventDefault();
                const theme = selector.getAttribute('data-theme');
                setTheme(theme);
            });
        });

        // Listen for system theme changes when auto is selected
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('theme') === 'auto') {
                htmlElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>