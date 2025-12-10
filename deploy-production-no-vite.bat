@echo off
REM Production Deployment Script - PTSP MTsN 2 Kota Malang (No Vite)
REM This script prepares the application for production deployment without Vite

echo Preparing application for production deployment (No Vite)...

REM Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

REM Install production dependencies only
composer install --optimize-autoloader --no-dev

REM Build front-end assets using Vite in production mode
npm install --production=false
npm run build

REM Set production environment with compiled assets
set APP_ENV=production
set ASSET_MODE=compiled
set CHECK_VITE_SERVER=false

REM Cache configuration for production
php artisan config:cache
php artisan event:cache

REM Run database migrations for production (be careful!)
REM Uncomment the following line when ready to run migrations in production
REM php artisan migrate --force

REM Optimize the application
php artisan optimize

REM Set proper permissions (adjust paths as needed for your server)
REM chmod -R 755 storage bootstrap/cache
REM chmod -R 775 storage/logs
REM chmod -R 775 storage/framework/cache
REM chmod -R 775 storage/framework/sessions

REM Generate application key if not present
php artisan key:generate --force

echo Production deployment (No Vite) preparation complete!
echo Remember to set proper file permissions for storage and bootstrap/cache directories
echo Ensure your web server is configured properly for Laravel
echo.
echo Asset system configured to use compiled assets instead of Vite in production