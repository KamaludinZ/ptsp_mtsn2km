#!/usr/bin/env bash
# Run on the hosting server after new application files are in place
# (release ZIP extracted, or `git pull` + scripts/build-production.sh).
# Needs a filled .env. Safe to run again.
#
#   scripts/post-deploy.sh            # migrate + caches
#   scripts/post-deploy.sh --seed     # also the initial data (first install only)
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "File .env belum ada. Salin .env.production.example lalu isi."; exit 1; }
grep -q '^APP_KEY=base64:' .env || php artisan key:generate --force

php artisan down --retry=60 || true
trap 'php artisan up' EXIT

mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
# Left by `npm run dev`: in production it makes pages load CSS/JS from a dev server
rm -f public/hot

php artisan optimize:clear
php artisan migrate --force
[ "${1:-}" = "--seed" ] && php artisan db:seed --force
php artisan storage:link --force >/dev/null 2>&1 || echo "Peringatan: storage:link gagal (symlink dimatikan hosting?), lihat docs/DEPLOYMENT.md."
php artisan optimize
php artisan filament:optimize >/dev/null 2>&1 || true

echo "Deploy selesai. Pastikan cron scheduler sudah terpasang (docs/DEPLOYMENT.md §6)."
