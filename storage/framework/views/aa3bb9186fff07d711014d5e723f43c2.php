<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
      data-theme="<?php echo e($themeAdmin ?? 'corporate'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php
        $appName = \App\Models\AppSetting::where('key', 'app_name')->value('value') ?? 'PTSP MTsN 2 KOTA MALANG';
    ?>

    <title><?php echo $__env->yieldContent('title', $appName . ' - Admin'); ?></title>

    <!-- Vite Assets (includes Bootstrap Icons locally) -->
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/css/bootstrap-custom.css', 'resources/css/admin.css', 'resources/js/bootstrap-bundle.js', 'resources/js/chart-bundle.js', 'resources/js/app.js']); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/app.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/bootstrap-custom.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/admin.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/bootstrap-bundle.js', false); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/chart-bundle.js', false); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/app.js', false); ?>

    <?php endif; ?>

    <style>
        /* --- RESPONSIVE BUTTON VISIBILITY --- */
        /* Hide Filament's default sidebar toggle buttons */
        .fi-sidebar-header .fi-icon-button {
            display: none !important;
        }

        /* Our custom desktop collapse button - hidden by default */
        #adminSidebarCollapseToggleDesktop {
            display: none;
        }

        @media (min-width: 1024px) {
            /* Show our custom desktop collapse button on desktop */
            #adminSidebarCollapseToggleDesktop {
                display: block;
            }
        }

        /* --- FILAMENT LAYOUT OVERRIDES --- */
        @media (min-width: 1024px) {
            /* Adjust sidebar width */
            .fi-layout .fi-sidebar {
                width: 16rem; /* Expanded width */
                flex-shrink: 0;
                transition: width 0.2s ease-in-out;
                position: relative; /* For positioning the collapse button */
            }

            .fi-layout.collapsed .fi-sidebar {
                width: 4rem; /* Collapsed width */
            }

            /* Adjust main content margin based on sidebar width */
            .fi-layout .fi-main-ctn {
                margin-left: 16rem; /* Adjust for expanded sidebar */
                transition: margin-left 0.2s ease-in-out;
            }

            .fi-layout.collapsed .fi-main-ctn {
                margin-left: 4rem; /* Adjust for collapsed sidebar */
            }
            
            /* --- SIDEBAR CUSTOMIZATIONS --- */
            /* Ensure single column layout for navigation */
            .fi-sidebar .fi-sidebar-nav {
                flex-direction: column;
                flex-wrap: nowrap;
                padding: 0.5rem;
                height: calc(100% - 4rem); /* Adjust for header height (e.g., 64px) */
                overflow-y: auto;
                overflow-x: hidden; /* Hide horizontal overflow */
            }

            /* Hide menu titles and text spans in collapsed state */
            .fi-layout.collapsed .fi-sidebar .fi-sidebar-nav .fi-sidebar-group > .fi-sidebar-group-label,
            .fi-layout.collapsed .fi-sidebar .fi-sidebar-nav .fi-sidebar-item-label,
            .fi-layout.collapsed .fi-sidebar-header .fi-logo .fi-logo-text {
                display: none;
            }

            /* Style for collapsed menu items */
            .fi-layout.collapsed .fi-sidebar .fi-sidebar-nav .fi-sidebar-item,
            .fi-layout.collapsed .fi-sidebar .fi-sidebar-nav .fi-sidebar-group > .fi-sidebar-group-header {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                width: 3rem; /* 48px square for hover area */
                height: 3rem; /* 48px square for hover area */
                margin: 0.25rem auto; /* Center with vertical spacing */
                border-radius: 0.5rem;
            }
            
            /* Adjust icon size and position in collapsed state */
            .fi-layout.collapsed .fi-sidebar .fi-sidebar-nav .fi-sidebar-item .fi-sidebar-item-icon {
                margin-inline-end: 0; /* Remove margin */
                font-size: 1.1rem; /* Consistent icon size */
                width: 1.5rem; /* Fixed width for alignment */
                text-align: center;
            }

            /* --- CUSTOM COLLAPSE BUTTON --- */
            #adminSidebarCollapseToggleDesktop {
                position: absolute;
                top: 50%;
                right: -1rem; /* Position it slightly outside for visual effect */
                transform: translateY(-50%);
                z-index: 40; /* Above sidebar, below overlay */
                background-color: var(--fi-sidebar-bg-color, #fff); /* Match sidebar background */
                border-radius: 0.5rem;
                border: 1px solid var(--fi-sidebar-border-color, #e5e7eb); /* Match sidebar border */
                box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            }
            /* Rotate collapse icon */
            .fi-layout.collapsed #adminSidebarCollapseToggleDesktop i {
                transform: rotate(180deg);
            }
            #adminSidebarCollapseToggleDesktop i {
                transition: transform 0.2s ease-in-out;
            }
        }

        /* Tooltip style (to be created by JS) */
        .sidebar-tooltip {
            position: fixed;
            white-space: nowrap;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            background-color: #ffffff !important;
            color: #000000 !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            transition-delay: 0.1s;
            pointer-events: none;
            z-index: 100;
        }
    </style>
</head>
<body class="bg-base-200">
    <div 
        x-data="{ isSidebarOpen: Alpine.store('sidebar.isOpen') ? Alpine.store('sidebar.isOpen') : true }" 
        x-bind:class="{ 'collapsed': !isSidebarOpen }"
        class="fi-layout flex min-h-screen w-full flex-row-reverse overflow-x-clip"
    >
        <div 
            class="fi-main-ctn w-screen flex-1 flex-col"
        >
            <!-- Topbar (replace with Filament's topbar or your custom one) -->
            <div class="fi-topbar">
                <div class="navbar bg-base-100 shadow-sm sticky top-0 z-10" role="navigation" aria-label="Top navigation">
                    <div class="flex-none">
                        <!-- Mobile Hamburger Button (Filament's built-in) -->
                        <?php if (isset($component)) { $__componentOriginalf0029cce6d19fd6d472097ff06a800a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon-button','data' => ['color' => 'gray','icon' => 'heroicon-o-bars-3','iconSize' => 'lg','label' => __('filament-panels::layout.actions.sidebar.open.label'),'xCloak' => true,'xData' => '{}','xOn:click' => '$store.sidebar.open()','xShow' => '! $store.sidebar.isOpen','class' => 'lg:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray','icon' => 'heroicon-o-bars-3','icon-size' => 'lg','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('filament-panels::layout.actions.sidebar.open.label')),'x-cloak' => true,'x-data' => '{}','x-on:click' => '$store.sidebar.open()','x-show' => '! $store.sidebar.isOpen','class' => 'lg:hidden']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0029cce6d19fd6d472097ff06a800a1)): ?>
<?php $attributes = $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1; ?>
<?php unset($__attributesOriginalf0029cce6d19fd6d472097ff06a800a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0029cce6d19fd6d472097ff06a800a1)): ?>
<?php $component = $__componentOriginalf0029cce6d19fd6d472097ff06a800a1; ?>
<?php unset($__componentOriginalf0029cce6d19fd6d472097ff06a800a1); ?>
<?php endif; ?>
                        <!-- Our custom desktop collapse button will be in the sidebar -->
                    </div>
                    <div class="flex-1 px-2 mx-2">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-xl font-bold text-primary">PTSP MTsN 2 KOTA MALANG</a>
                    </div>
                    <div class="flex-none">
                        <!-- Notifications -->
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-circle" role="button" aria-haspopup="true">
                                <div class="indicator">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                    <?php
                                        $pendingTicketsCount = \App\Models\Ticket::where('status', 'pending')->count();
                                        $pendingApprovalsCount = \App\Models\Ticket::where('status', 'pending_approval')->count();
                                        $complaintsCount = \App\Models\Complaint::where('status', 'pending')->count();
                                        $totalUnreadNotifications = $pendingTicketsCount + $pendingApprovalsCount + $complaintsCount;
                                    ?>
                                    <?php if($totalUnreadNotifications > 0): ?>
                                        <span class="badge badge-xs badge-error indicator-item"><?php echo e($totalUnreadNotifications); ?></span>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-72" role="menu">
                                <?php if($pendingTicketsCount > 0): ?>
                                    <li>
                                        <a href="<?php echo e(route('admin.tickets.index')); ?>?status=pending" role="menuitem">
                                            <div class="flex items-center">
                                                <div class="mr-3 p-2 rounded-full bg-warning/10 text-warning">
                                                    <i class="bi bi-ticket-perforated"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium"><?php echo e($pendingTicketsCount); ?> Tiket Baru</div>
                                                    <div class="text-xs text-base-content/70">Menunggu verifikasi</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if($pendingApprovalsCount > 0): ?>
                                    <li>
                                        <a href="<?php echo e(route('admin.tickets.index')); ?>?status=pending_approval" role="menuitem">
                                            <div class="flex items-center">
                                                <div class="mr-3 p-2 rounded-full bg-primary/10 text-primary">
                                                    <i class="bi bi-check-circle"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium"><?php echo e($pendingApprovalsCount); ?> Persetujuan Menunggu</div>
                                                    <div class="text-xs text-base-content/70">Menunggu approval</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if($complaintsCount > 0): ?>
                                    <li>
                                        <a href="<?php echo e(route('admin.complaints.index')); ?>?status=pending" role="menuitem">
                                            <div class="flex items-center">
                                                <div class="mr-3 p-2 rounded-full bg-error/10 text-error">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium"><?php echo e($complaintsCount); ?> Pengaduan Masuk</div>
                                                    <div class="text-xs text-base-content/70">Menunggu ditanggapi</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if($totalUnreadNotifications === 0): ?>
                                    <li><span class="text-center text-base-content/70" role="menuitem">Tidak ada notifikasi baru</span></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <!-- User Dropdown -->
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-circle avatar" role="button" aria-haspopup="true">
                                <div class="w-10 rounded-full">
                                    <img src="<?php echo e(auth()->user()->avatar && str_contains(auth()->user()->avatar, 'ui-avatars.com') ? auth()->user()->avatar : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=amber'); ?>" />
                                </div>
                            </label>
                            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52" role="menu">
                                <li>
                                    <a href="<?php echo e(route('admin.dashboard')); ?>" role="menuitem">
                                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="justify-between" href="<?php echo e(route('profile.edit')); ?>" role="menuitem">
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
                                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" role="menuitem">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> 
            <main class="fi-main mx-auto h-full w-full px-4 md:px-6 lg:px-8">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
            <footer>
                <footer class="footer footer-center p-4 bg-base-300 text-base-content">
                    <aside>
                        <p>v1.0.0 © 2025 PTSP MTsN 2 KOTA MALANG. Hak Cipta Dilindungi. | Dikembangkan dengan ❤️ oleh Tim PUSKOM</p>
                    </aside>
                </footer>
            </footer>
        </div>
        <!-- Sidebar -->
        <aside 
            x-cloak
            x-data="{}"
            x-bind:class="{
                'fi-sidebar-open': Alpine.store('sidebar.isOpen'),
                '-translate-x-full rtl:translate-x-full': ! Alpine.store('sidebar.isOpen'),
                'lg:sticky lg:translate-x-0 rtl:lg:-translate-x-0': true
            }"
            class="fi-sidebar fixed inset-y-0 start-0 z-30 flex flex-col h-screen content-start bg-white transition-all dark:bg-gray-900 lg:z-0 lg:bg-transparent lg:shadow-none lg:ring-0 lg:transition-none dark:lg:bg-transparent"
        >
            <div class="overflow-x-clip">
                <header class="fi-sidebar-header flex h-16 items-center bg-white px-6 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 lg:shadow-sm">
                    <div x-show="Alpine.store('sidebar.isOpen')" x-transition:enter="lg:transition lg:delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <a <?php echo e(\Filament\Support\generate_href_html(route('admin.dashboard'))); ?>>
                            <?php if (isset($component)) { $__componentOriginalb501e8c73315a10eb0eb5fd14fda0d94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb501e8c73315a10eb0eb5fd14fda0d94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.logo','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb501e8c73315a10eb0eb5fd14fda0d94)): ?>
<?php $attributes = $__attributesOriginalb501e8c73315a10eb0eb5fd14fda0d94; ?>
<?php unset($__attributesOriginalb501e8c73315a10eb0eb5fd14fda0d94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb501e8c73315a10eb0eb5fd14fda0d94)): ?>
<?php $component = $__componentOriginalb501e8c73315a10eb0eb5fd14fda0d94; ?>
<?php unset($__componentOriginalb501e8c73315a10eb0eb5fd14fda0d94); ?>
<?php endif; ?>
                        </a>
                    </div>
                </header>
            </div>
            <nav class="fi-sidebar-nav flex-grow flex flex-col gap-y-7 overflow-y-auto overflow-x-hidden px-6 py-8" style="scrollbar-gutter: stable">
                <!-- Our existing menu items will go here -->
                <ul class="min-h-full bg-base-200 text-base-content" role="navigation" aria-label="Main navigation">
                    <li class="menu-title">Menu</li>
                    <li><a class="<?php if(request()->routeIs('admin.dashboard')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.dashboard')); ?>" role="menuitem"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a></li>
                    <li class="menu-title">Master Data</li>
                    <li><a class="<?php if(request()->routeIs('admin.services.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.services.index')); ?>" role="menuitem"><i class="bi bi-cone-striped"></i> <span>Layanan</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.service-categories.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.service-categories.index')); ?>" role="menuitem"><i class="bi bi-tags"></i> <span>Kategori Layanan</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.pengumuman.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.pengumuman.index')); ?>" role="menuitem"><i class="bi bi-megaphone"></i> <span>Pengumuman</span></a></li>
                    <li class="menu-title">Manajemen Tiket</li>
                    <li><a class="<?php if(request()->routeIs('admin.tickets.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.tickets.index')); ?>" role="menuitem"><i class="bi bi-ticket-detailed"></i> <span>Tiket Layanan</span></a></li>
                    <li class="menu-title">Pengunjung</li>
                    <li><a class="<?php if(request()->routeIs('admin.visitors.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.visitors.index')); ?>" role="menuitem"><i class="bi bi-person-walking"></i> <span>Buku Tamu</span></a></li>
                    <li class="menu-title">Pengaduan</li>
                    <li><a class="<?php if(request()->routeIs('admin.complaints.*') && !request()->routeIs('admin.whistleblowing.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.complaints.index')); ?>" role="menuitem"><i class="bi bi-chat-left-text"></i> <span>Pengaduan Masyarakat</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.whistleblowing.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.whistleblowing.index')); ?>" role="menuitem"><i class="bi bi-shield-exclamation"></i> <span>Whistleblowing</span></a></li>
                    <li class="menu-title">Pengawasan & Evaluasi</li>
                    <li><a class="<?php if(request()->routeIs('admin.survey.management')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.survey.management')); ?>" role="menuitem"><i class="bi bi-clipboard-check"></i> <span>Manajemen Survey</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.skm.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.skm.report')); ?>" role="menuitem"><i class="bi bi-graph-up"></i> <span>Laporan SKM</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.spak.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.spak.report')); ?>" role="menuitem"><i class="bi bi-shield-check"></i> <span>Laporan SPAK</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.performance.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.performance.report')); ?>" role="menuitem"><i class="bi bi-bar-chart-line"></i> <span>Laporan Kinerja</span></a></li>
                    <li class="menu-title">Manajemen Pengguna</li>
                    <li><a class="<?php if(request()->routeIs('admin.users.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.users.index')); ?>" role="menuitem"><i class="bi bi-people"></i> <span>Manajemen User</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.roles.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.roles.index')); ?>" role="menuitem"><i class="bi bi-person-check"></i> <span>Manajemen Role</span></a></li>
                    <li class="menu-title">Keamanan</li>
                    <li><a class="<?php if(request()->routeIs('admin.security.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.security.dashboard')); ?>" role="menuitem"><i class="bi bi-shield-lock"></i> <span>Security Dashboard</span></a></li>
                    <li class="menu-title">Konfigurasi</li>
                    <li><a class="<?php if(request()->routeIs('admin.settings.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.settings.index')); ?>" role="menuitem"><i class="bi bi-gear"></i> <span>Pengaturan Umum</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.roles.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.roles.index')); ?>" role="menuitem"><i class="bi bi-person-check"></i> <span>Hak Akses</span></a></li>
                    <li><a class="<?php if(request()->routeIs('admin.users.*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.users.index')); ?>" role="menuitem"><i class="bi bi-people"></i> <span>Manajemen User</span></a></li>
                </ul>
            </nav>
            <!-- Custom Collapse Button -->
            <button id="adminSidebarCollapseToggleDesktop" class="btn btn-square btn-ghost absolute right-0 top-1/2 -translate-y-1/2 -me-4 z-40" aria-label="Toggle Admin Sidebar Collapse">
                <i class="fas fa-angle-double-left"></i>
            </button>
        </aside>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const adminLayout = document.querySelector('.fi-layout');
            const adminSidebarCollapseToggleDesktop = document.getElementById('adminSidebarCollapseToggleDesktop');
            const ADMIN_SIDEBAR_COLLAPSED_KEY = 'admin_sidebar_collapsed';

            // Initialize Filament's sidebar store if not present
            if (typeof Alpine !== 'undefined' && typeof Alpine.store('sidebar') === 'undefined') {
                Alpine.store('sidebar', {
                    isOpen: true,
                    open: function () { this.isOpen = true },
                    close: function () { this.isOpen = false },
                    toggle: function () { this.isOpen = !this.isOpen },
                });
            }

            // Apply saved state for collapsed admin sidebar
            if (localStorage.getItem(ADMIN_SIDEBAR_COLLAPSED_KEY) === 'true') {
                adminLayout.classList.add('collapsed');
                Alpine.store('sidebar').isOpen = false; // Sync with Filament's store
            }

            // --- Toggle Function ---
            function toggleAdminSidebar() {
                adminLayout.classList.toggle('collapsed');
                const isCollapsed = adminLayout.classList.contains('collapsed');
                localStorage.setItem(ADMIN_SIDEBAR_COLLAPSED_KEY, isCollapsed);
                Alpine.store('sidebar').isOpen = !isCollapsed; // Sync with Filament's store
            }

            if (adminSidebarCollapseToggleDesktop) {
                adminSidebarCollapseToggleDesktop.addEventListener('click', toggleAdminSidebar);
            }

            // --- Tooltip Logic for Collapsed Sidebar ---
            let tooltipElement = null;

            function createTooltip() {
                if (!tooltipElement) {
                    tooltipElement = document.createElement('div');
                    tooltipElement.classList.add('sidebar-tooltip');
                    document.body.appendChild(tooltipElement);
                }
            }

            function showTooltip(event) {
                const isCollapsed = adminLayout.classList.contains('collapsed');
                if (!isCollapsed || window.innerWidth < 1024) return;

                const menuItem = event.currentTarget;
                const textSpan = menuItem.querySelector('span');
                if (!textSpan || !textSpan.textContent) return;

                createTooltip();
                
                tooltipElement.textContent = textSpan.textContent;
                
                const rect = menuItem.getBoundingClientRect();
                tooltipElement.style.left = `${rect.right + 10}px`;
                tooltipElement.style.top = `${rect.top + (rect.height / 2)}px`;
                tooltipElement.style.transform = 'translateY(-50%)';
                tooltipElement.style.opacity = '1';
                tooltipElement.style.visibility = 'visible';
            }

            function hideTooltip() {
                if (tooltipElement) {
                    tooltipElement.style.opacity = '0';
                    tooltipElement.style.visibility = 'hidden';
                }
            }

            // Target Filament's actual menu links
            const menuLinks = document.querySelectorAll('.fi-sidebar .fi-sidebar-nav .fi-sidebar-item');
            menuLinks.forEach(link => {
                link.addEventListener('mouseenter', showTooltip);
                link.addEventListener('mouseleave', hideTooltip);
            });
        });
    </script>
</body>
</html><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/layouts/admin.blade.php ENDPATH**/ ?>