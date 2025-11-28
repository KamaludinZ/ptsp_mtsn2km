@echo off
echo PTSP MTsN 2 KOTA MALANG - Production Installation Script
echo.

echo This script helps with the initial setup of the application
echo IMPORTANT: Run this from the application root directory
echo.

echo Setting up application...
composer install --no-dev --optimize-autoloader

echo.
echo Generating application key...
php artisan key:generate

echo.
echo Running database migrations...
php artisan migrate --force

echo.
echo Caching configuration...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo Installation complete!
echo Please review your .env file to ensure all settings are correct
echo.

pause