@echo off
echo ========================================
echo  Setup Local Assets - PTSP MTsN 2
echo ========================================
echo.

echo [1/5] Installing Node Dependencies...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: npm install failed!
    pause
    exit /b 1
)
echo SUCCESS: Dependencies installed
echo.

echo [2/5] Building Assets for Production...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: npm build failed!
    pause
    exit /b 1
)
echo SUCCESS: Assets built
echo.

echo [3/5] Clearing Laravel Caches...
call php artisan cache:clear
call php artisan config:clear
call php artisan view:clear
call php artisan route:clear
echo SUCCESS: Caches cleared
echo.

echo [4/5] Verifying Build...
if exist "public\build\manifest.json" (
    echo SUCCESS: Build manifest found
) else (
    echo ERROR: Build manifest not found!
    pause
    exit /b 1
)
echo.

echo [5/5] Setup Complete!
echo.
echo ========================================
echo  Next Steps:
echo ========================================
echo 1. Update your layout files to use @vite directive
echo 2. Remove CDN links from views
echo 3. Test with: php artisan serve
echo 4. Check console (F12) for any errors
echo.
echo See MIGRASI_LOCAL_ASSETS.md for detailed guide
echo ========================================
echo.
pause
