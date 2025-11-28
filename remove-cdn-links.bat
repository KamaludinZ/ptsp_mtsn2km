@echo off
echo ========================================
echo  Remove CDN Links - PTSP MTsN 2
echo ========================================
echo.
echo This will update all view files to use local assets
echo instead of CDN (fonts.bunny.net, cdn.jsdelivr.net, etc)
echo.
pause

echo Creating backup...
xcopy /E /I /Y resources\views resources\views.backup
echo Backup created in resources\views.backup
echo.

echo Updating view files...

:: Update welcome.blade.php
echo [1/9] Updating welcome.blade.php...
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<link rel=\"preconnect\" href=\"https://fonts\.bunny\.net\">', '{{-- Preconnect removed - using local assets --}}' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<link rel=\"preload\" href=\"https://fonts\.bunny\.net.*\" as=\"style\">', '{{-- Preload fonts removed - using local assets --}}' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<link href=\"https://fonts\.bunny\.net.*\" rel=\"stylesheet\">', '{{-- Google Fonts removed - using local assets --}}' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<link href=\"https://cdn\.jsdelivr\.net/npm/bootstrap.*\" rel=\"stylesheet\".*>', '@vite([''resources/css/bootstrap-custom.css'', ''resources/js/bootstrap-bundle.js''])' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<link rel=\"stylesheet\" href=\"https://cdnjs\.cloudflare\.com/ajax/libs/font-awesome.*\".*>', '{{-- Font Awesome now in bootstrap-bundle.js --}}' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<link href=\"https://unpkg\.com/aos.*\" rel=\"stylesheet\">', '{{-- AOS now in bootstrap-bundle.js --}}' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<script src=\"https://cdn\.jsdelivr\.net/npm/bootstrap.*\"></script>', '{{-- Bootstrap JS in bootstrap-bundle.js --}}' | Set-Content 'resources\views\welcome.blade.php'"
powershell -Command "(Get-Content 'resources\views\welcome.blade.php') -replace '<script src=\"https://unpkg\.com/aos.*\"></script>', '{{-- AOS JS in bootstrap-bundle.js --}}' | Set-Content 'resources\views\welcome.blade.php'"

:: Update layouts/public.blade.php
echo [2/9] Updating layouts/public.blade.php...
powershell -Command "(Get-Content 'resources\views\layouts\public.blade.php') -replace '<link.*fonts\.bunny\.net.*>', '@vite([''resources/css/bootstrap-custom.css'', ''resources/js/bootstrap-bundle.js'', ''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\layouts\public.blade.php'"
powershell -Command "(Get-Content 'resources\views\layouts\public.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '{{-- Bootstrap in bundle --}}' | Set-Content 'resources\views\layouts\public.blade.php'"
powershell -Command "(Get-Content 'resources\views\layouts\public.blade.php') -replace '<script.*cdn\.jsdelivr\.net.*</script>', '{{-- Bootstrap JS in bundle --}}' | Set-Content 'resources\views\layouts\public.blade.php'"

:: Update layouts/app.blade.php
echo [3/9] Updating layouts/app.blade.php...
powershell -Command "(Get-Content 'resources\views\layouts\app.blade.php') -replace '<link.*fonts\.bunny\.net.*>', '@vite([''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\layouts\app.blade.php'"
powershell -Command "(Get-Content 'resources\views\layouts\app.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '{{-- Using Vite assets --}}' | Set-Content 'resources\views\layouts\app.blade.php'"

:: Update layouts/guest.blade.php
echo [4/9] Updating layouts/guest.blade.php...
powershell -Command "(Get-Content 'resources\views\layouts\guest.blade.php') -replace '<link.*fonts\.bunny\.net.*>', '@vite([''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\layouts\guest.blade.php'"
powershell -Command "(Get-Content 'resources\views\layouts\guest.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '{{-- Using Vite assets --}}' | Set-Content 'resources\views\layouts\guest.blade.php'"

:: Update layouts/auth.blade.php
echo [5/9] Updating layouts/auth.blade.php...
powershell -Command "(Get-Content 'resources\views\layouts\auth.blade.php') -replace '<link.*fonts\.bunny\.net.*>', '@vite([''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\layouts\auth.blade.php'"
powershell -Command "(Get-Content 'resources\views\layouts\auth.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '{{-- Using Vite assets --}}' | Set-Content 'resources\views\layouts\auth.blade.php'"

:: Update errors/layout.blade.php
echo [6/9] Updating errors/layout.blade.php...
powershell -Command "(Get-Content 'resources\views\errors\layout.blade.php') -replace '<link.*fonts\.bunny\.net.*>', '@vite([''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\errors\layout.blade.php'"

:: Update backoffice/layout.blade.php
echo [7/9] Updating backoffice/layout.blade.php...
powershell -Command "(Get-Content 'resources\views\backoffice\layout.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '@vite([''resources/css/bootstrap-custom.css'', ''resources/js/bootstrap-bundle.js'', ''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\backoffice\layout.blade.php'"

:: Update frontdesk/layout.blade.php
echo [8/9] Updating frontdesk/layout.blade.php...
powershell -Command "(Get-Content 'resources\views\frontdesk\layout.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '@vite([''resources/css/bootstrap-custom.css'', ''resources/js/bootstrap-bundle.js'', ''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\frontdesk\layout.blade.php'"

:: Update supervision/layout.blade.php
echo [9/9] Updating supervision/layout.blade.php...
powershell -Command "(Get-Content 'resources\views\supervision\layout.blade.php') -replace '<link.*cdn\.jsdelivr\.net.*>', '@vite([''resources/css/bootstrap-custom.css'', ''resources/js/bootstrap-bundle.js'', ''resources/css/app.css'', ''resources/js/app.js''])' | Set-Content 'resources\views\supervision\layout.blade.php'"

echo.
echo ========================================
echo  Update Complete!
echo ========================================
echo.
echo Backup: resources\views.backup
echo.
echo Next steps:
echo 1. Run: npm install
echo 2. Run: npm run build
echo 3. Run: php artisan view:clear
echo 4. Test your application
echo.
echo If anything goes wrong, restore from backup:
echo   xcopy /E /I /Y resources\views.backup resources\views
echo.
pause
