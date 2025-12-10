<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'PTSP MTsN 2 Kota Malang')); ?></title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#14532d">

    
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/bootstrap-custom.css', 'resources/css/app.css', 'resources/css/dark-mode.css', 'resources/css/accessibility.css', 'resources/js/bootstrap-bundle.js', 'resources/js/app.js', 'resources/js/accessibility.js']); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/bootstrap-custom.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/app.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/dark-mode.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/accessibility.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/bootstrap-bundle.js', false); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/app.js', false); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/accessibility.js', false); ?>

    <?php endif; ?>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <!-- Floating Action Buttons - Left Side -->
    <div class="floating-action-buttons-left">
        <!-- Accessibility Button -->
        <button id="accessibility-btn"
                class="floating-btn accessibility-btn pulse">
            <span style="line-height: 1;">♿</span>
        </button>

        <!-- Back to Top Button -->
        <button onclick="scrollToTop()"
                id="back-to-top-btn"
                class="floating-btn back-to-top-btn">
            <i class="fas fa-arrow-up"></i>
        </button>

        <!-- WhatsApp Button -->
        <a href="https://wa.me/<?php echo e(config('app.whatsapp_number', '6285183367500')); ?>?text=<?php echo e(urlencode('Halo, saya ingin bertanya tentang layanan PTSP MTsN 2 KOTA MALANG')); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="floating-btn whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
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

    <!-- Page Content -->
    <main id="main-content" class="py-4">
        <?php echo e($slot); ?>

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

    

    <!-- Custom JavaScript -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
        });

        // Theme management
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon();

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const themeIcon = document.getElementById('themeIcon');
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (themeIcon) {
                themeIcon.className = currentTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
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
</body>
</html><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\onlineportal\layout.blade.php ENDPATH**/ ?>