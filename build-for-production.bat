@echo off
REM Production build script untuk PTSP MTsN 2 Kota Malang
REM Script ini build assets menggunakan Vite untuk deployment production

echo ========================================
echo PTSP MTsN 2 - Production Build
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo ERROR: File .env tidak ditemukan!
    pause
    exit /b 1
)

echo Step 1: Install dependencies...
echo.
call composer install --optimize-autoloader --no-dev
if errorlevel 1 (
    echo ERROR: composer install gagal!
    pause
    exit /b 1
)

call npm ci --production=false
if errorlevel 1 (
    echo ERROR: npm install gagal!
    pause
    exit /b 1
)

echo.
echo Step 2: Clear all cache...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear
call php artisan route:clear

echo.
echo Step 3: Build assets dengan Vite...
call npm run build
if errorlevel 1 (
    echo ERROR: Vite build gagal!
    pause
    exit /b 1
)

echo.
echo Step 4: Optimize Laravel...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

echo.
echo Step 5: Run migrations (jika diperlukan)...
set /p RUN_MIGRATE="Jalankan migrasi database? (y/n): "
if /i "%RUN_MIGRATE%"=="y" (
    call php artisan migrate --force
)

echo.
echo ========================================
echo Production Build Selesai!
echo ========================================
echo.
echo File build ada di folder: public/build
echo.
echo Langkah selanjutnya:
echo 1. Upload semua file ke server production
echo 2. Set APP_ENV=production di .env production
echo 3. Set APP_DEBUG=false di .env production
echo 4. Set ASSET_MODE=build di .env production
echo 5. Pastikan folder storage dan bootstrap/cache writable
echo.
pause
