<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
      data-theme="<?php echo e(\App\Models\AppSetting::where('key', 'theme_public')->value('value') ?? 'light'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php
        $appName = \App\Models\AppSetting::where('key', 'app_name')->value('value') ?? config('app.name', 'PTSP MTsN 2 Kota Malang');
        $appFavicon = \App\Models\AppSetting::where('key', 'app_favicon')->value('value') ?? 'favicon.ico';
    ?>

    <title><?php echo $__env->yieldContent('title', $appName); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('description', 'Pelayanan Terpadu Satu Pintu - ' . $appName); ?>">

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(Storage::disk('public')->exists($appFavicon) && $appFavicon != 'favicon.ico' ? asset('storage/' . $appFavicon) : asset('images/kemenag-favicon.ico')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#14532d">

    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/inline-style-fix.css']); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/inline-style-fix.css'); ?>

    <?php endif; ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="font-normal">
    <!-- MARKER: layouts.public -->
    <!-- Page Loading Overlay -->
    <div id="page-loading-overlay">
        <div class="loading-content">
            <!-- Modern Spinner -->
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>

            <!-- Loading Text -->
            <div class="loading-text">PTSP MTsN 2 Kota Malang</div>
            <div class="loading-subtext">Memuat Halaman...</div>

            <!-- Loading Dots -->
            <div class="loading-dots">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>

            <!-- Progress Bar -->
            <div class="loading-progress">
                <div class="loading-progress-bar"></div>
            </div>
        </div>
    </div>

    <?php
        // Get contact settings from database
        $contactAddress = \App\Models\AppSetting::where('key', 'contact_address')->value('value') ?? 'Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur';
        $contactPhone = \App\Models\AppSetting::where('key', 'contact_phone')->value('value') ?? '(0341) 711500';
        $contactEmail = \App\Models\AppSetting::where('key', 'contact_email')->value('value') ?? 'mtsnmalang2adm@gmail.com';
        $contactWebsite = \App\Models\AppSetting::where('key', 'contact_website')->value('value') ?? 'www.mtsn2kotamalang.sch.id';
        $contactWhatsappPtsp = \App\Models\AppSetting::where('key', 'contact_whatsapp_ptsp')->value('value') ?? '6285183367500';
        $contactWhatsappPengaduan = \App\Models\AppSetting::where('key', 'contact_whatsapp_pengaduan')->value('value') ?? '6285183375008';
    ?>

    <!-- Contact Header -->
    <div class="bg-gray-800 text-gray-200 dark:text-gray-300 py-2 text-xs" role="banner" aria-label="Informasi Kontak">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 1rem;">
            <!-- Desktop View - Centered -->
            <div class="hidden md:flex items-center justify-center gap-4 flex-wrap">
                <div class="flex items-center whitespace-nowrap">
                    <i class="fas fa-map-marker-alt" style="color: #ea580c; margin-right: 0.5rem;"></i>
                    <span><?php echo e($contactAddress); ?></span>
                </div>
                <div class="flex items-center whitespace-nowrap">
                    <i class="fas fa-phone" style="color: #ea580c; margin-right: 0.5rem;"></i>
                    <span><?php echo e($contactPhone); ?></span>
                </div>
                <div class="flex items-center whitespace-nowrap">
                    <i class="fas fa-envelope" style="color: #ea580c; margin-right: 0.5rem;"></i>
                    <span><?php echo e($contactEmail); ?></span>
                </div>
                <div class="flex items-center whitespace-nowrap">
                    <i class="fas fa-globe" style="color: #ea580c; margin-right: 0.5rem;"></i>
                    <span><?php echo e($contactWebsite); ?></span>
                </div>
                <div class="flex items-center whitespace-nowrap">
                    <i class="fas fa-comment" style="color: #ea580c; margin-right: 0.5rem;"></i>
                    <span><?php echo e(preg_replace('/^62/', '0', $contactWhatsappPtsp)); ?> (PTSP)</span>
                </div>
                <div class="flex items-center whitespace-nowrap">
                    <i class="fas fa-comment" style="color: #ea580c; margin-right: 0.5rem;"></i>
                    <span><?php echo e(preg_replace('/^62/', '0', $contactWhatsappPengaduan)); ?> (Pengaduan)</span>
                </div>
            </div>

            <!-- Mobile View - Dropdown -->
            <div class="md:hidden flex items-center justify-center">
                <div style="position: relative;">
                    <button type="button" onclick="toggleContactDropdown()" class="btn btn-ghost btn-xs" style="color: #d1d5db;">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Info Kontak</span>
                        <i class="fas fa-chevron-down ml-1" id="contactChevron"></i>
                    </button>
                    <ul id="contactDropdownMenu" class="dropdown-content menu menu-sm p-2 shadow bg-gray-800 dark:bg-gray-900 rounded-box w-52 mt-4 z-100 border border-gray-700 dark:border-gray-600" style="display: none; position: absolute; left: 50%; transform: translateX(-50%); border-radius: 0.5rem; padding: 0.5rem; margin-top: 0.25rem; width: 16rem; z-index: 1000; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                        <li class="contact-dropdown-item" style="list-style: none; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #d1d5db; font-size: 0.75rem;">
                                <i class="fas fa-map-marker-alt" style="color: #ea580c;"></i>
                                <span><?php echo e($contactAddress); ?></span>
                            </div>
                        </li>
                        <li class="contact-dropdown-item" style="list-style: none; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #d1d5db; font-size: 0.75rem;">
                                <i class="fas fa-phone" style="color: #ea580c;"></i>
                                <span><?php echo e($contactPhone); ?></span>
                            </div>
                        </li>
                        <li class="contact-dropdown-item" style="list-style: none; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #d1d5db; font-size: 0.75rem;">
                                <i class="fas fa-envelope" style="color: #ea580c;"></i>
                                <span><?php echo e($contactEmail); ?></span>
                            </div>
                        </li>
                        <li class="contact-dropdown-item" style="list-style: none; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #d1d5db; font-size: 0.75rem;">
                                <i class="fas fa-globe" style="color: #ea580c;"></i>
                                <span><?php echo e($contactWebsite); ?></span>
                            </div>
                        </li>
                        <li class="contact-dropdown-item" style="list-style: none; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #d1d5db; font-size: 0.75rem;">
                                <i class="fas fa-comment" style="color: #ea580c;"></i>
                                <span><?php echo e(preg_replace('/^62/', '0', $contactWhatsappPtsp)); ?> (PTSP)</span>
                            </div>
                        </li>
                        <li class="contact-dropdown-item" style="list-style: none; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #d1d5db; font-size: 0.75rem;">
                                <i class="fas fa-comment" style="color: #ea580c;"></i>
                                <span><?php echo e(preg_replace('/^62/', '0', $contactWhatsappPengaduan)); ?> (Pengaduan)</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link" aria-label="Lewati ke konten utama">Lewati ke konten utama</a>

    <!-- Floating Action Buttons - Left Side -->
    <div class="floating-action-buttons-left">
        <!-- Accessibility Button -->
        <button id="accessibility-btn"
                class="floating-btn accessibility-btn pulse"
                aria-label="Buka panel aksesibilitas">
            <i class="fas fa-universal-access"></i>
        </button>

        <!-- Back to Top Button -->
        <button onclick="scrollToTop()"
                id="back-to-top-btn"
                class="floating-btn back-to-top-btn">
            <i class="fas fa-arrow-up"></i>
        </button>

        <!-- WhatsApp Button with Dropdown -->
        <div class="whatsapp-dropdown">
            <button type="button" class="floating-btn whatsapp-btn" id="whatsapp-toggle">
                <img src="<?php echo e(asset('images/WhatsApp.webp')); ?>" alt="WhatsApp" class="w-full h-full object-contain">
            </button>
            <div class="whatsapp-dropdown-menu" id="whatsapp-dropdown-menu">
                <!-- Info Jam Operasional -->
                <div class="whatsapp-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Layanan WhatsApp Aktif Sesuai Jam Operasional</span>
                </div>
                <div class="whatsapp-option">
                    <a href="https://wa.me/6285183367500?text=Halo, saya ingin bertanya tentang layanan PTSP MTsN 2 KOTA MALANG"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-dropdown-link">
                        <img src="<?php echo e(asset('images/WhatsApp.webp')); ?>" alt="WhatsApp" class="whatsapp-icon"> PTSP (085183367500)
                    </a>
                </div>
                <div class="whatsapp-option">
                    <a href="https://wa.me/6285156631610?text=Halo, saya ingin bertanya tentang layanan Komite MTsN 2 KOTA MALANG"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-dropdown-link">
                        <img src="<?php echo e(asset('images/WhatsApp.webp')); ?>" alt="WhatsApp" class="whatsapp-icon"> Komite (085156631610)
                    </a>
                </div>
                <div class="whatsapp-option">
                    <a href="https://wa.me/6285183375008?text=Halo, saya ingin menyampaikan pengaduan ke MTsN 2 KOTA MALANG"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-dropdown-link">
                        <img src="<?php echo e(asset('images/WhatsApp.webp')); ?>" alt="WhatsApp" class="whatsapp-icon"> Pengaduan (085183375008)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Accessibility Panel -->
    <div id="accessibilityPanel" class="accessibility-panel">
        <!-- Panel Header -->
        <div class="accessibility-panel-header">
            <h3 class="accessibility-panel-title">
                <span>🎯</span>
                <span>Pusat Aksesibilitas</span>
            </h3>
            <p class="accessibility-panel-subtitle">WCAG 2.1 AA Compliant • Inklusif untuk Semua</p>
            <button onclick="toggleAccessibilitySidebar()" class="accessibility-close-btn" aria-label="Tutup panel aksesibilitas">
                ✕
            </button>
        </div>

        <!-- Panel Content -->
        <div class="accessibility-panel-content">
            <!-- Font Size Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>📝</span>
                    <span>Ukuran Teks</span>
                </h4>
                <div class="accessibility-controls">
                    <button onclick="setFontSize('small')" class="font-size-btn" id="fontSmallBtn">A-</button>
                    <button onclick="setFontSize('normal')" class="font-size-btn active" id="fontNormalBtn">A</button>
                    <button onclick="setFontSize('large')" class="font-size-btn" id="fontLargeBtn">A+</button>
                    <button onclick="setFontSize('extra-large')" class="font-size-btn" id="fontXLargeBtn">A++</button>
                </div>
            </div>

            <!-- Visual Adjustments Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>🎨</span>
                    <span>Penyesuaian Visual</span>
                </h4>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>👁️</span>
                        <span>Kontras Tinggi</span>
                    </div>
                    <div class="accessibility-switch" id="highContrastControl" onclick="toggleHighContrast()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🔗</span>
                        <span>Garis Bawah Link</span>
                    </div>
                    <div class="accessibility-switch" id="underlineControl" onclick="toggleUnderline()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>📏</span>
                        <span>Jarak Baris</span>
                    </div>
                    <div class="accessibility-switch" id="lineHeightControl" onclick="toggleLineHeight()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>↔️</span>
                        <span>Jarak Huruf</span>
                    </div>
                    <div class="accessibility-switch" id="letterSpacingControl" onclick="toggleLetterSpacing()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🖱️</span>
                        <span>Kursor Besar</span>
                    </div>
                    <div class="accessibility-switch" id="cursorControl" onclick="toggleLargeCursor()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>
            </div>

            <!-- Advanced Features Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>⚙️</span>
                    <span>Fitur Lanjutan</span>
                </h4>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🎬</span>
                        <span>Nonaktifkan Animasi</span>
                    </div>
                    <div class="accessibility-switch" id="animationControl" onclick="toggleAnimations()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>⌨️</span>
                        <span>Navigasi Keyboard Ditingkatkan</span>
                    </div>
                    <div class="accessibility-switch" id="keyboardControl" onclick="toggleKeyboardNavigation()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>
            </div>

            <!-- Reset Section -->
            <div class="accessibility-section">
                <button onclick="resetAllAccessibility()" class="btn btn-danger w-100" aria-label="Reset semua pengaturan aksesibilitas">
                    <i class="fas fa-sync-alt me-2"></i>
                    Reset Semua Pengaturan
                </button>
            </div>
        </div>
    </div>

    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer role="contentinfo" class="bg-gray-800 text-gray-200 dark:text-gray-300 py-10 px-4">
        <div class="max-w-screen-xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 bg-transparent">
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <?php if(config('app.logo')): ?>
                            <img src="<?php echo e(asset(config('app.logo'))); ?>" alt="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> Logo"
                                 class="h-12 w-12 object-contain rounded-lg bg-white p-1">
                        <?php else: ?>
                            <img src="<?php echo e(asset('images/kemenag-logo.png')); ?>" alt="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> Logo"
                                 class="h-12 w-12 object-contain rounded-lg bg-white p-1">
                        <?php endif; ?>
                        <div>
                            <h3 class="font-bold text-white text-base"><?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?></h3>
                            <p class="text-sm opacity-80">Pelayanan Terpadu Satu Pintu</p>
                        </div>
                    </div>
                    <p class="text-sm opacity-80">
                        Sistem pelayanan terpadu sesuai Permen PANRB 15/2014 untuk kemudahan akses layanan.
                    </p>
                    <div class="flex gap-4 mt-4">
                        <a href="#" class="text-2xl text-white" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-2xl text-white" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-2xl text-white" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-2xl text-white" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <nav class="bg-transparent">
                    <h6 class="font-bold uppercase mb-4 text-white">Kontak Kami</h6> 
                    <div class="flex items-start gap-2 text-sm mb-2 text-white"><i class="fas fa-map-marker-alt mt-1 text-orange-500"></i><span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-phone text-orange-500"></i><span>(0341) 711500</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-envelope text-orange-500"></i><span>mtsnmalang2adm@gmail.com</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-globe text-orange-500"></i><span>www.mtsn2kotamalang.sch.id</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-comment text-orange-500"></i><span>0851 8336 7500 (PTSP)</span></div>
                    <div class="flex items-center gap-2 text-sm text-white"><i class="fas fa-comment text-orange-500"></i><span>0851 8337 5008 (Pengaduan)</span></div>
                </nav>

                <nav class="bg-transparent">
                    <h6 class="font-bold uppercase mb-4 text-white">Jam Operasional</h6>
                    <div class="text-sm">
                        <div class="flex items-start gap-2 mb-2 text-white">
                            <i class="fas fa-clock mt-1 text-orange-500"></i>
                            <div>
                                <strong class="text-white">Senin - Kamis</strong><br>
                                <span class="opacity-80">07.00 - 15.00 WIB</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 mb-2 text-white">
                            <i class="fas fa-clock mt-1 text-orange-500"></i>
                            <div>
                                <strong class="text-white">Jumat</strong><br>
                                <span class="opacity-80">07.00 - 11.00 WIB</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 text-white">
                            <i class="fas fa-calendar-times mt-1 text-orange-500"></i>
                            <div>
                                <strong class="text-white">Sabtu - Minggu</strong><br>
                                <span class="opacity-80">Tutup</span>
                            </div>
                        </div>
                    </div>
                </nav>

                <nav class="bg-transparent">
                    <h6 class="font-bold uppercase mb-4 text-white">Link Terkait</h6> 
                    <a href="https://kemenag.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>Kementerian Agama RI</span></a>
                    <a href="https://kanwil.kemenag.go.id/jatim" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>Kanwil Kemenag Jatim</span></a>
                    <a href="https://kankemenag.malangkota.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>Kemenag Kota Malang</span></a>
                    <a href="https://lapor.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>SP4N Lapor</span></a>
                    <a href="https://sippn.menpan.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>SIPPN Menpan</span></a>
                </nav>
            </div>
            <div class="mt-10 pt-10 border-t border-gray-700 dark:border-gray-600">
                <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-left gap-4">
                    <p class="text-sm opacity-80">
                        <i class="fas fa-code-branch mr-2"></i>
                        <span class="mr-3">v1.0.0</span>
                        &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?>. Hak Cipta Dilindungi.
                    </p>
                    <p class="text-sm opacity-80">
                        <i class="fas fa-code mr-1"></i> Dikembangkan dengan <i class="fas fa-heart text-red-500 mx-1"></i> oleh Tim PUSKOM
                    </p>
                </div>
            </div>
        </div>
    </footer>

    
    <script>
        // Contact Dropdown Toggle for Mobile
        function toggleContactDropdown() {
            const menu = document.getElementById('contactDropdownMenu');
            const chevron = document.getElementById('contactChevron');

            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            } else {
                menu.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('contactDropdownMenu');
            const btn = event.target.closest('button[onclick="toggleContactDropdown()"]');

            if (!btn && menu && menu.style.display === 'block') {
                menu.style.display = 'none';
                const chevron = document.getElementById('contactChevron');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        // Initialize AOS - Optimized for performance
        if (typeof AOS !== 'undefined') {
            // Disable AOS on mobile devices for better performance
            const isMobile = window.innerWidth < 768;
            if (!isMobile) {
                AOS.init({
                    duration: 400, // Reduced from 800ms
                    once: true,
                    disable: 'mobile', // Disable on mobile
                    offset: 50, // Reduced offset for faster trigger
                    delay: 0, // No delay
                    easing: 'ease-out', // Simpler easing
                    mirror: false, // No animation when scrolling back
                    anchorPlacement: 'top-bottom',
                    startEvent: 'DOMContentLoaded',
                    animatedClassName: 'aos-animate',
                    initClassName: 'aos-init',
                });
            }
        }

        // Accessibility Panel Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const accessibilityPanel = document.getElementById('accessibilityPanel');
            const accessibilityBtn = document.getElementById('accessibility-btn');

            if (accessibilityBtn) {
                accessibilityBtn.addEventListener('click', function() {
                    toggleAccessibilitySidebar();
                });
            }

            // Close panel when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInsidePanel = accessibilityPanel.contains(event.target);
                const isClickOnButton = accessibilityBtn.contains(event.target);

                if (!isClickInsidePanel && !isClickOnButton && accessibilityPanel.classList.contains('open')) {
                    toggleAccessibilitySidebar();
                }
            });

            // Load saved accessibility preferences
            loadAccessibilityPreferences();
        });

        function toggleAccessibilitySidebar() {
            const panel = document.getElementById('accessibilityPanel');
            const btn = document.getElementById('accessibility-btn');

            if (panel.classList.contains('open')) {
                // Close panel
                panel.classList.remove('open');
                btn.innerHTML = '<i class="fas fa-universal-access"></i>';
                btn.classList.add('pulse');
                btn.setAttribute('aria-label', 'Buka panel aksesibilitas');
            } else {
                // Open panel
                panel.classList.add('open');
                btn.innerHTML = '<i class="fas fa-times"></i>';
                btn.classList.remove('pulse');
                btn.setAttribute('aria-label', 'Tutup panel aksesibilitas');
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide back to top button
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.getElementById('back-to-top-btn');
            if (window.pageYOffset > 300) {
                backToTopBtn.style.opacity = '1';
                backToTopBtn.style.visibility = 'visible';
            } else {
                backToTopBtn.style.opacity = '0';
                backToTopBtn.style.visibility = 'hidden';
            }
        });

        // Font Size Functions
        function setFontSize(size) {
            const body = document.body;
            body.className = body.className.replace(/font-\w+/g, '');
            body.classList.add('font-' + size);

            // Update active button
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('font' + size.charAt(0).toUpperCase() + size.slice(1).replace('-', '') + 'Btn').classList.add('active');

            localStorage.setItem('fontSize', size);
        }

        // Toggle Functions
        function toggleHighContrast() {
            document.body.classList.toggle('high-contrast');
            toggleSwitch('highContrastControl');
            localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
        }

        function toggleUnderline() {
            document.body.classList.toggle('underline-links');
            toggleSwitch('underlineControl');
            localStorage.setItem('underlineLinks', document.body.classList.contains('underline-links'));
        }

        function toggleLineHeight() {
            document.body.classList.toggle('increased-line-height');
            toggleSwitch('lineHeightControl');
            localStorage.setItem('lineHeight', document.body.classList.contains('increased-line-height'));
        }

        function toggleLetterSpacing() {
            document.body.classList.toggle('increased-letter-spacing');
            toggleSwitch('letterSpacingControl');
            localStorage.setItem('letterSpacing', document.body.classList.contains('increased-letter-spacing'));
        }

        function toggleLargeCursor() {
            document.body.classList.toggle('large-cursor');
            toggleSwitch('cursorControl');
            localStorage.setItem('largeCursor', document.body.classList.contains('large-cursor'));
        }

        function toggleAnimations() {
            document.body.classList.toggle('no-animations');
            toggleSwitch('animationControl');
            localStorage.setItem('noAnimations', document.body.classList.contains('no-animations'));
        }

        function toggleKeyboardNavigation() {
            document.body.classList.toggle('keyboard-navigation');
            toggleSwitch('keyboardControl');
            localStorage.setItem('keyboardNavigation', document.body.classList.contains('keyboard-navigation'));
        }

        function toggleSwitch(id) {
            const switchEl = document.getElementById(id);
            switchEl.classList.toggle('active');
            const isActive = switchEl.classList.contains('active');
            switchEl.setAttribute('aria-checked', isActive);
        }

        function resetAllAccessibility() {
            // Remove all accessibility classes
            document.body.className = 'font-normal';

            // Reset all switches
            document.querySelectorAll('.accessibility-switch').forEach(sw => {
                sw.classList.remove('active');
                sw.setAttribute('aria-checked', 'false');
            });

            // Reset font size buttons
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('fontNormalBtn').classList.add('active');

            // Clear localStorage
            localStorage.removeItem('fontSize');
            localStorage.removeItem('highContrast');
            localStorage.removeItem('underlineLinks');
            localStorage.removeItem('lineHeight');
            localStorage.removeItem('letterSpacing');
            localStorage.removeItem('largeCursor');
            localStorage.removeItem('noAnimations');
            localStorage.removeItem('keyboardNavigation');
        }

        function loadAccessibilityPreferences() {
            // Load font size
            const fontSize = localStorage.getItem('fontSize');
            if (fontSize) {
                setFontSize(fontSize);
            }

            // Load toggles
            if (localStorage.getItem('highContrast') === 'true') {
                document.body.classList.add('high-contrast');
                document.getElementById('highContrastControl').classList.add('active');
            }
            if (localStorage.getItem('underlineLinks') === 'true') {
                document.body.classList.add('underline-links');
                document.getElementById('underlineControl').classList.add('active');
            }
            if (localStorage.getItem('lineHeight') === 'true') {
                document.body.classList.add('increased-line-height');
                document.getElementById('lineHeightControl').classList.add('active');
            }
            if (localStorage.getItem('letterSpacing') === 'true') {
                document.body.classList.add('increased-letter-spacing');
                document.getElementById('letterSpacingControl').classList.add('active');
            }
            if (localStorage.getItem('largeCursor') === 'true') {
                document.body.classList.add('large-cursor');
                document.getElementById('cursorControl').classList.add('active');
            }
            if (localStorage.getItem('noAnimations') === 'true') {
                document.body.classList.add('no-animations');
                document.getElementById('animationControl').classList.add('active');
            }
            if (localStorage.getItem('keyboardNavigation') === 'true') {
                document.body.classList.add('keyboard-navigation');
                document.getElementById('keyboardControl').classList.add('active');
            }
        }

        // Language Toggle
        function changeLanguage(lang) {
            console.log('Language changed to:', lang);
            // Make an AJAX request to change the language
            fetch(`/set-locale/${lang}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    // Reload the page to apply the new language
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error changing language:', error);
            });
        }

    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')([
            'resources/css/bootstrap-custom.css',
            'resources/css/app.css',
            'resources/css/fontawesome.css',
            'resources/css/dark-mode.css',
            'resources/css/public-layout.css',
            'resources/css/accessibility.css',
            'resources/css/loading.css',
            'resources/js/bootstrap-bundle.js',
            'resources/js/app.js',
            'resources/js/accessibility.js'
        ]); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/bootstrap-custom.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/app.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/fontawesome.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/dark-mode.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/public-layout.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/accessibility.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/loading.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/bootstrap-bundle.js', false); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/app.js', false); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/accessibility.js', false); ?>

    <?php endif; ?>

    <script>
        // Hide loading overlay when page is fully loaded
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('page-loading-overlay');
            if (loadingOverlay) {
                // Add a small delay for smoother transition
                setTimeout(function() {
                    loadingOverlay.classList.add('hidden');
                }, 300);
            }
        });

        // Fallback: Hide loading after maximum 5 seconds even if load event hasn't fired
        setTimeout(function() {
            const loadingOverlay = document.getElementById('page-loading-overlay');
            if (loadingOverlay && !loadingOverlay.classList.contains('hidden')) {
                loadingOverlay.classList.add('hidden');
            }
        }, 5000);
    </script>
</body>
</html>
<?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/layouts/public.blade.php ENDPATH**/ ?>