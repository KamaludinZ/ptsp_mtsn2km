@echo off
REM Build and optimize the application on the production server (PostgreSQL).
REM Requires a configured .env (see .env.production.example).
setlocal
cd /d "%~dp0\.."

call php artisan down --retry=60

call composer install --no-dev --optimize-autoloader --no-interaction || goto :error
call npm ci || goto :error
call npm run build || goto :error

call php artisan migrate --force || goto :error
call php artisan storage:link --no-interaction
call php artisan filament:upgrade --no-interaction || goto :error

call php artisan optimize:clear
call php artisan optimize || goto :error

call php artisan up
echo Build produksi selesai.
exit /b 0

:error
echo Build gagal. Aplikasi tetap dalam mode maintenance; perbaiki lalu jalankan: php artisan up
exit /b 1
