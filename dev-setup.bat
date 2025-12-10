@echo off
echo ========================================
echo  Development Setup - PTSP MTsN 2
echo ========================================
echo.

REM Set development environment
set APP_ENV=local
set ASSET_MODE=vite
set CHECK_VITE_SERVER=true

echo [Step 1/4] Installing Dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: composer install failed!
    pause
    exit /b 1
)
echo SUCCESS: Dependencies installed
echo.

echo [Step 2/4] Installing Node Dependencies...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: npm install failed!
    pause
    exit /b 1
)
echo SUCCESS: Node dependencies installed
echo.

echo [Step 3/4] Starting Vite Development Server...
start cmd /k "npm run dev"

echo [Step 4/4] Starting Laravel Development Server...
php artisan serve

echo ========================================
echo  Development Environment Ready!
echo  Vite server running on: http://localhost:5173
echo  Laravel server running on: http://127.0.0.1:8000
echo ========================================