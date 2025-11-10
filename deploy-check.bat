@echo off
echo ========================================
echo  Deployment Checklist - PTSP MTsN 2
echo ========================================
echo.

set /a errors=0

echo Checking production requirements...
echo.

echo [1/10] Checking public/build directory...
if exist "public\build\manifest.json" (
    echo [OK] Build manifest found
) else (
    echo [FAIL] Build manifest not found! Run: npm run build
    set /a errors+=1
)
echo.

echo [2/10] Checking vendor directory...
if exist "vendor\autoload.php" (
    echo [OK] Composer dependencies installed
) else (
    echo [FAIL] Vendor not found! Run: composer install
    set /a errors+=1
)
echo.

echo [3/10] Checking .env file...
if exist ".env" (
    echo [OK] .env file exists
) else (
    echo [FAIL] .env file missing! Copy from .env.example
    set /a errors+=1
)
echo.

echo [4/10] Checking storage permissions...
if exist "storage\logs" (
    echo [OK] Storage directory exists
) else (
    echo [FAIL] Storage directory missing!
    set /a errors+=1
)
echo.

echo [5/10] Checking bootstrap/cache permissions...
if exist "bootstrap\cache" (
    echo [OK] Bootstrap cache directory exists
) else (
    echo [FAIL] Bootstrap cache directory missing!
    set /a errors+=1
)
echo.

echo [6/10] Checking APP_KEY...
findstr /C:"APP_KEY=base64:" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] APP_KEY is set
) else (
    echo [WARN] APP_KEY might not be set. Run: php artisan key:generate
)
echo.

echo [7/10] Checking database connection...
findstr /C:"DB_CONNECTION=" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Database configuration found
) else (
    echo [WARN] Database not configured
)
echo.

echo [8/10] Checking APP_ENV...
findstr /C:"APP_ENV=production" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] APP_ENV set to production
) else (
    echo [WARN] APP_ENV not set to production
)
echo.

echo [9/10] Checking APP_DEBUG...
findstr /C:"APP_DEBUG=false" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] APP_DEBUG is false
) else (
    echo [WARN] APP_DEBUG should be false in production
)
echo.

echo [10/10] Checking compiled assets...
dir /b "public\build\assets" 2>nul | findstr /R "app-.*\.css" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Compiled CSS assets found
) else (
    echo [FAIL] No compiled CSS assets! Run: npm run build
    set /a errors+=1
)
echo.

echo ========================================
echo  Deployment Checklist Summary
echo ========================================
if %errors% equ 0 (
    echo [SUCCESS] All checks passed!
    echo Application is ready for deployment.
) else (
    echo [ERROR] %errors% check(s) failed!
    echo Please fix the issues before deploying.
)
echo ========================================
echo.
pause
