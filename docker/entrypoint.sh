#!/bin/sh
# Container start-up for the PTSP app: check configuration, wait for
# PostgreSQL, migrate, seed on the very first deploy and warm the caches.
#
# Environment switches (all optional):
#   AUTO_MIGRATE=true       run "php artisan migrate --force" on start (default true)
#   SEED_ON_FIRST_DEPLOY=true  seed roles, admin, services and surveys when the
#                           users table is still empty (default true)
#   DB_WAIT_TIMEOUT=60      seconds to wait for the database (default 60)
set -eu

cd /var/www/html

log() { echo "[entrypoint] $*"; }
artisan() { su-exec www-data php artisan "$@" --no-interaction; }

if [ -z "${APP_KEY:-}" ]; then
    log "ERROR: APP_KEY belum diisi. Buat dengan: php artisan key:generate --show"
    log "       lalu isi variabel APP_KEY di Coolify (format base64:...)."
    exit 1
fi

# The storage volume may be empty on the first start: recreate the tree.
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

log "Menunggu database..."
timeout="${DB_WAIT_TIMEOUT:-60}"
elapsed=0
until su-exec www-data php docker/db-check.php ready >/dev/null 2>&1; do
    elapsed=$((elapsed + 2))
    if [ "$elapsed" -ge "$timeout" ]; then
        log "ERROR: database tidak dapat dihubungi setelah ${timeout} detik:"
        su-exec www-data php docker/db-check.php ready || true
        exit 1
    fi
    sleep 2
done
log "Database siap."

# Drop compiled files left in the storage volume by a previous version.
# (Not optimize:clear: that also flushes the database cache store, whose
# table does not exist yet on the very first deploy.)
for cmd in config:clear route:clear view:clear event:clear; do
    artisan "$cmd" >/dev/null
done

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    log "Menjalankan migrasi..."
    artisan migrate --force
fi

if [ "${SEED_ON_FIRST_DEPLOY:-true}" = "true" ] && su-exec www-data php docker/db-check.php empty; then
    log "Database baru: mengisi peran, akun admin, layanan dan survei..."
    artisan db:seed --force
fi

artisan storage:link --force >/dev/null 2>&1 || true

log "Menyiapkan cache konfigurasi, route, view dan event..."
artisan optimize
artisan filament:optimize >/dev/null 2>&1 || true

log "Aplikasi siap di port 8080."
exec "$@"
