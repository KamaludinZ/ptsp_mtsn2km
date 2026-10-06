#!/usr/bin/env bash
# Build an upload-ready release ZIP for hosting without Docker (cPanel/VPS):
# production vendor/ and compiled assets included, so the server needs
# neither Composer nor Node.js. The same ZIP can be uploaded on
# Monitoring Sistem → Pembaruan (composer.json sits at the root).
#
#   scripts/build-release.sh            # version from `git describe`
#   scripts/build-release.sh v1.4.0     # or an explicit version
#
# Result: dist/ptsp-<version>.zip and its .sha256. Only committed files are
# packed (git archive), never .env, storage contents, tests or node_modules.
set -euo pipefail
cd "$(dirname "$0")/.."

command -v composer >/dev/null || { echo "composer tidak ditemukan"; exit 1; }
command -v npm >/dev/null || { echo "npm tidak ditemukan"; exit 1; }
command -v zip >/dev/null || { echo "zip tidak ditemukan (apt install zip)"; exit 1; }

version="${1:-$(git describe --tags --always --dirty 2>/dev/null || date +%Y%m%d)}"
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "Peringatan: ada perubahan yang belum di-commit; paket hanya berisi yang sudah di-commit."
fi

work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT
app="$work/app"
mkdir -p "$app" dist

echo "→ Menyalin berkas yang di-commit (HEAD)"
git archive HEAD | tar -x -C "$app"

echo "→ Composer (tanpa paket dev)"
composer install --working-dir="$app" --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts --ignore-platform-req=php

echo "→ Aset Vite"
(cd "$app" && npm ci --no-audit --no-fund --silent && npm run build --silent)

echo "→ Paket Laravel & aset Filament"
(cd "$app" && APP_KEY="base64:$(openssl rand -base64 32)" APP_ENV=production php artisan package:discover --ansi >/dev/null \
    && APP_KEY="base64:$(openssl rand -base64 32)" APP_ENV=production php artisan filament:assets --ansi >/dev/null)

echo "→ Merapikan isi paket"
(cd "$app" && rm -rf node_modules tests .github docker Dockerfile docker-compose*.yml .dockerignore \
    phpunit.xml* .env.docker.example .ngodingpakeai .ngodingpakeaiignore AGENTS.md bootstrap/cache/*.php \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions \
                storage/framework/views storage/logs bootstrap/cache \
    && printf '%s\n' "$version" > RELEASE)

out="dist/ptsp-${version}.zip"
rm -f "$out"
(cd "$app" && zip -qr -X "$OLDPWD/$out" . -x '*.DS_Store')
sha256sum "$out" | tee "$out.sha256"
echo "Selesai: $out ($(du -h "$out" | cut -f1)). Lihat docs/DEPLOYMENT.md untuk langkah unggah."
