@echo off
REM Production Deployment Script for PTSP MTsN 2 Kota Malang
REM This script prepares the application for production deployment

echo Preparing application for production deployment...

REM Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

REM Install production dependencies only
composer install --optimize-autoloader --no-dev

REM Set production environment
php artisan config:cache
php artisan event:cache

REM Run database migrations (be careful in production!)
REM Uncomment the following line when ready to run migrations in production
REM php artisan migrate --force

REM Optimize the application
php artisan optimize

REM Set proper permissions (adjust paths as needed for your server)
REM chmod -R 755 storage bootstrap/cache
REM chmod -R 775 storage/logs
REM chmod -R 775 storage/framework/cache
REM chmod -R 775 storage/framework/sessions
REM chmod -R 775 storage/framework/views

REM Generate application key if not present
php artisan key:generate --force

echo Production deployment preparation complete!
echo Remember to set proper file permissions for storage and bootstrap/cache directories
echo Ensure your web server is configured properly for Laravel