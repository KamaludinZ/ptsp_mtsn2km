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

# Uploaded documents live in storage/: without a volume they vanish on the
# next redeploy (Coolify's Dockerfile build pack adds none by itself).
if ! grep -q " /var/www/html/storage " /proc/mounts; then
    log "PERINGATAN: /var/www/html/storage BUKAN volume permanen. Dokumen dan gambar yang diunggah"
    log "            akan HILANG saat redeploy. Coolify: Persistent Storage -> Volume Mount,"
    log "            destination /var/www/html/storage, lalu Redeploy."
fi

# The storage volume may be empty on the first start: recreate the tree.
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

log "Menunggu database $(su-exec www-data php docker/db-check.php target 2>/dev/null || echo '(konfigurasi tidak terbaca)')..."
wait_limit="${DB_WAIT_TIMEOUT:-60}"
started=$(date +%s)
# Each attempt is capped (an unreachable host must not hang the start-up);
# the reason is printed every ~10 s so it shows in the Coolify log.
until reason=$(timeout 10 su-exec www-data php docker/db-check.php ready 2>&1); do
    elapsed=$(( $(date +%s) - started ))
    [ -n "$reason" ] || reason="tidak ada jawaban dalam 10 detik (host/jaringan salah?)"
    if [ "$elapsed" -ge "$wait_limit" ]; then
        log "ERROR: database tidak dapat dihubungi setelah ${elapsed} detik: ${reason}"
        log "       Periksa DATABASE_URL (pakai 'Postgres URL (internal)' dari Coolify) atau DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD,"
        log "       dan pastikan database berjalan di server/jaringan yang sama."
        exit 1
    fi
    if [ $(( elapsed / 10 )) -ne "${last_report:--1}" ]; then
        last_report=$(( elapsed / 10 ))
        log "Database belum siap (${elapsed} detik): ${reason}"
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
