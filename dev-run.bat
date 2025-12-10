@echo off
REM Development script untuk PTSP MTsN 2 Kota Malang
REM Script ini menjalankan Laravel serve dan Vite dev server bersamaan

echo ========================================
echo PTSP MTsN 2 Kota Malang - Development
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo ERROR: File .env tidak ditemukan!
    echo Silakan copy .env.example menjadi .env terlebih dahulu
    pause
    exit /b 1
)

REM Check if node_modules exists
if not exist node_modules (
    echo Node modules tidak ditemukan. Menjalankan npm install...
    call npm install
    if errorlevel 1 (
        echo ERROR: npm install gagal!
        pause
        exit /b 1
    )
)

REM Check if vendor exists
if not exist vendor (
    echo Vendor tidak ditemukan. Menjalankan composer install...
    call composer install
    if errorlevel 1 (
        echo ERROR: composer install gagal!
        pause
        exit /b 1
    )
)

echo.
echo Memastikan .env menggunakan mode Vite...
findstr /C:"ASSET_MODE=vite" .env >nul
if errorlevel 1 (
    echo ASSET_MODE=vite >> .env
    echo CHECK_VITE_SERVER=true >> .env
)

echo.
echo Clear cache Laravel...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear

echo.
echo ========================================
echo Starting Development Servers...
echo ========================================
echo Laravel: http://localhost:8000
echo Vite HMR: http://localhost:5173
echo ========================================
echo.
echo Tekan Ctrl+C untuk menghentikan server
echo.

REM Start Laravel serve and Vite dev server
start /B cmd /c "php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 2 /nobreak >nul
start /B cmd /c "npm run dev"

echo.
echo Server sudah berjalan!
echo Buka browser di: http://localhost:8000
echo.
echo Tekan Ctrl+C untuk menghentikan, atau tutup window ini
echo.

REM Keep window open
pause
