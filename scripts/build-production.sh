#!/usr/bin/env bash
# Build and optimize the application on the production server (PostgreSQL).
# Requires a configured .env (see .env.production.example).
set -euo pipefail
cd "$(dirname "$0")/.."

php artisan down --retry=60 || true

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force
php artisan storage:link --no-interaction || true
php artisan filament:upgrade --no-interaction

php artisan optimize:clear
php artisan optimize

php artisan up
echo "Build produksi selesai."
