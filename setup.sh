#!/bin/bash
# Setup script for PTSP MTsN 2 KOTA MALANG

# Install Laravel and required packages
composer install

# Generate application key
php artisan key:generate

# Install Laravel Breeze with Blade and authentication
composer require laravel/breeze --dev
php artisan breeze:install blade

# Install Filament
composer require filament/filament:"^3.0"
php artisan filament:install --panels

# Install spatie/laravel-permission for role management
composer require spatie/laravel-permission

# Install other required packages
composer require barryvdh/laravel-dompdf
composer require yajra/laravel-datatables-oracle

# Publish the permissions config file (optional)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Create the permissions table
php artisan migrate

# Run the install command to set up Filament
php artisan filament:install --panels

# Create a symbolic link for storage
php artisan storage:link

echo "Setup completed! Please update your .env file with database credentials and run 'php artisan migrate' to create tables."