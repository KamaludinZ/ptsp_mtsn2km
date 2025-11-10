@echo off
echo ========================================
echo  Starting Development Servers
echo  PTSP MTsN 2 Kota Malang
echo ========================================
echo.
echo This will start both Vite and Laravel servers
echo.
echo Vite Dev Server: http://localhost:5173
echo Laravel Server:  http://localhost:8000
echo.
echo Press Ctrl+C to stop both servers
echo ========================================
echo.

:: Start Vite in a new window
start "Vite Dev Server" cmd /k "npm run dev"

:: Wait a bit for Vite to start
timeout /t 3 /nobreak >nul

:: Start Laravel server
echo Starting Laravel development server...
php artisan serve

pause
