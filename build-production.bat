@echo off
echo ========================================
echo  Production Build - PTSP MTsN 2 (No Vite)
echo ========================================
echo.

echo [Step 1/7] Installing Dependencies...
call composer install --optimize-autoloader --no-dev
if %errorlevel% neq 0 (
    echo ERROR: composer install failed!
    pause
    exit /b 1
)
echo SUCCESS: Dependencies installed
echo.

echo [Step 2/7] Installing Node Dependencies...
call npm install --production=false
if %errorlevel% neq 0 (
    echo ERROR: npm install failed!
    pause
    exit /b 1
)
echo SUCCESS: Node dependencies installed
echo.

echo [Step 3/7] Building Assets for Production...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: Build failed!
    pause
    exit /b 1
)
echo SUCCESS: Assets compiled
echo.

echo [Step 4/7] Regenerating Composer Autoload...
call composer dump-autoload --optimize
if %errorlevel% neq 0 (
    echo ERROR: Composer autoload failed!
    pause
    exit /b 1
)
echo SUCCESS: Autoload regenerated
echo.

echo [Step 5/7] Clearing Laravel Caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
call php artisan cache:clear
echo SUCCESS: Caches cleared
echo.

echo [Step 6/7] Optimizing Laravel...
REM Set environment to production and asset mode to compiled
set ASSET_MODE=compiled
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo SUCCESS: Laravel optimized
echo.

echo [Step 7/7] Verifying Build...
if exist "public\build\manifest.json" (
    echo SUCCESS: Build manifest found
) else (
    echo ERROR: Build manifest not found!
    pause
    exit /b 1
)

if exist "vendor\autoload.php" (
    echo SUCCESS: Composer autoload verified
) else (
    echo ERROR: Composer autoload not found!
    pause
    exit /b 1
)
echo.

echo ========================================
echo  Production Build Complete! (No Vite)
echo ========================================
echo.
echo Build artifacts:
echo - public/build/      (compiled assets)
echo - vendor/            (composer dependencies)
echo - node_modules/      (node dependencies) - Can be removed in final deployment
echo.
echo Next steps for deployment:
echo 1. Copy entire project to server (excluding node_modules for final deployment)
echo 2. Set proper permissions (storage, bootstrap/cache)
echo 3. Configure .env for production
echo    - APP_ENV=production
echo    - ASSET_MODE=compiled
echo    - CHECK_VITE_SERVER=false
echo 4. Run: php artisan migrate --force
echo 5. Run: php artisan optimize
echo.
echo Asset system now uses compiled assets instead of Vite in production!
echo ========================================
echo.
pause
