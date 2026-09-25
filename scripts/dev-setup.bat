@echo off
REM Local development setup for PTSP MTsN 2 Kota Malang (PostgreSQL, e.g. Laragon).
REM Prerequisites: PHP 8.2+ with pdo_pgsql, Composer, Node.js 20+, PostgreSQL.
REM Create the database first:  psql -U postgres -f database\setup_postgres.sql
setlocal
cd /d "%~dp0\.."

if not exist .env copy .env.example .env

call composer install || goto :error
call php artisan key:generate --no-interaction || goto :error
call npm ci || goto :error
call npm run build || goto :error

call php artisan migrate --seed --no-interaction || goto :error
call php artisan storage:link --no-interaction
call php artisan filament:upgrade --no-interaction || goto :error

echo.
echo Selesai. Jalankan: php artisan serve
echo Admin demo: ptsp@mtsn2malang.sch.id / admin123 (hanya untuk lokal)
exit /b 0

:error
echo Setup gagal. Periksa pesan di atas.
exit /b 1
