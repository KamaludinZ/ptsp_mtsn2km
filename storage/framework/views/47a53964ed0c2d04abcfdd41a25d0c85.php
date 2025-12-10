<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'PTSP MTsN 2 Kota Malang')); ?></title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#14532d">

    
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')([
            'resources/css/bootstrap-custom.css',
            'resources/js/bootstrap-bundle.js',
            'resources/css/app.css',
            'resources/js/app.js'
        ]); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/bootstrap-custom.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/bootstrap-bundle.js', false); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/app.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/app.js', false); ?>

    <?php endif; ?>

    <!-- Custom Styles -->
    <style>
        :root {
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
            --bs-gray-900: #111827;
            --bs-gray-800: #1f2937;
            --bs-gray-700: #374151;
            --bs-gray-600: #4b5563;

            --bs-focus: #2563eb;
            --bs-text: #1f2937;
            --bs-bg: #ffffff;
            --bs-surface: #f9fafb;
            --bs-border: #e5e7eb;

            --bs-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
        }

        [data-theme="dark"] {
            --bs-text: #f9fafb;
            --bs-bg: #111827;
            --bs-surface: #1f2937;
            --bs-border: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--bs-font-sans);
            background: var(--bs-surface);
            color: var(--bs-text);
            min-height: 100vh;
        }

        [data-theme="dark"] body {
            background: #111827;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php echo $__env->yieldContent('content'); ?>

    

    <!-- Theme Toggle Script -->
    <script>
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
</html>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\layouts\auth.blade.php ENDPATH**/ ?>