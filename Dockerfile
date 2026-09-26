# syntax=docker/dockerfile:1.7
#
# Production image for PTSP MTsN 2 Kota Malang (Laravel 12 + PostgreSQL).
# One container runs nginx + php-fpm + the Laravel scheduler (supervisord).
# Build:  docker build -t ptsp .
# Deploy: see docs/DEPLOY_COOLIFY.md

ARG PHP_VERSION=8.3
ARG NODE_VERSION=22

############################################
# 1. PHP dependencies (no dev packages)
############################################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev --no-interaction --no-progress --prefer-dist \
        --no-scripts --no-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

############################################
# 2. Front-end assets (Vite)
############################################
FROM node:${NODE_VERSION}-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js postcss.config.cjs tailwind.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

############################################
# 3. Runtime
############################################
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

# Extensions: pgsql (database), intl (Filament), gd/exif (images, PDF),
# zip (Excel export), bcmath, pcntl (queue/scheduler signals), opcache.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql intl gd exif zip bcmath pcntl opcache \
    && apk add --no-cache nginx supervisor su-exec curl tzdata \
    && rm -rf /var/cache/apk/* \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    ASSET_MODE=compiled \
    CHECK_VITE_SERVER=false \
    TZ=Asia/Jakarta \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_FPM_MAX_CHILDREN=10 \
    TRUSTED_PROXIES=*

WORKDIR /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

# Package discovery runs here (skipped in the vendor stage); no .env is
# baked into the image, configuration comes from the environment.
RUN rm -f .env bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=90s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
