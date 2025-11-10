@echo off
echo ========================================
echo  Production Build - PTSP MTsN 2
echo ========================================
echo.

echo [Step 1/6] Installing Dependencies...
call npm install --production=false
if %errorlevel% neq 0 (
    echo ERROR: npm install failed!
    pause
    exit /b 1
)
echo SUCCESS: Dependencies installed
echo.

echo [Step 2/6] Building Assets for Production...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: Build failed!
    pause
    exit /b 1
)
echo SUCCESS: Assets compiled
echo.

echo [Step 3/6] Regenerating Composer Autoload...
call composer dump-autoload --optimize
if %errorlevel% neq 0 (
    echo ERROR: Composer autoload failed!
    pause
    exit /b 1
)
echo SUCCESS: Autoload regenerated
echo.

echo [Step 4/6] Clearing Laravel Caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
call php artisan cache:clear
echo SUCCESS: Caches cleared
echo.

echo [Step 5/6] Optimizing Laravel...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo SUCCESS: Laravel optimized
echo.

echo [Step 6/6] Verifying Build...
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
echo  Production Build Complete!
echo ========================================
echo.
echo Build artifacts:
echo - public/build/      (compiled assets)
echo - vendor/            (composer dependencies)
echo - node_modules/      (node dependencies)
echo.
echo Next steps for deployment:
echo 1. Copy entire project to server
echo 2. Set proper permissions (storage, bootstrap/cache)
echo 3. Configure .env for production
echo 4. Run: php artisan migrate --force
echo 5. Set APP_ENV=production in .env
echo.
echo NO NEED to install npm or run Vite on server!
echo All assets are already compiled in public/build/
echo ========================================
echo.
pause
