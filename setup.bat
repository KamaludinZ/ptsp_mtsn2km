@echo off
REM Setup script for PTSP MTsN 2 KOTA MALANG

echo Installing Laravel and required packages...
composer install

echo Generating application key...
php artisan key:generate

echo Installing Laravel Breeze with Blade and authentication...
composer require laravel/breeze --dev
php artisan breeze:install blade

echo Installing Filament...
composer require filament/filament:"^3.0"
php artisan filament:install --panels

echo Installing spatie/laravel-permission for role management...
composer require spatie/laravel-permission

echo Installing other required packages...
composer require barryvdh/laravel-dompdf
composer require yajra/laravel-datatables-oracle

echo Publishing the permissions config file...
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

echo Creating the permissions table...
php artisan migrate

echo Setting up Filament panels...
php artisan filament:install --panels

echo Creating a symbolic link for storage...
php artisan storage:link

echo Setup completed! Please update your .env file with database credentials and run 'php artisan migrate' to create tables.
pause