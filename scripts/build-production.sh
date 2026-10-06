#!/usr/bin/env bash
# Build on the production server itself (needs Composer and Node.js there),
# then migrate and warm the caches. Requires a filled .env
# (see .env.production.example). Without Composer/Node on the server, build a
# release ZIP elsewhere with scripts/build-release.sh instead.
set -euo pipefail
cd "$(dirname "$0")/.."

composer install --no-dev --optimize-autoloader --no-interaction
npm ci --no-audit --no-fund
npm run build
php artisan filament:assets

scripts/post-deploy.sh "$@"
