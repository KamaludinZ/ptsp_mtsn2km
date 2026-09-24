#!/usr/bin/env bash
# Local development setup for PTSP MTsN 2 Kota Malang (PostgreSQL).
# Prerequisites: PHP 8.2+ with pdo_pgsql, Composer, Node.js 20+, PostgreSQL.
# Create the database first:  psql -U postgres -f database/setup_postgres.sql
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || cp .env.example .env

composer install
php artisan key:generate --no-interaction
npm ci
npm run build

php artisan migrate --seed --no-interaction
php artisan storage:link --no-interaction || true
php artisan filament:upgrade --no-interaction

echo
echo "Selesai. Jalankan: php artisan serve"
echo "Admin demo: ptsp@mtsn2malang.sch.id / admin123 (hanya untuk lokal)"
