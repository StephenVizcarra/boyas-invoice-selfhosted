# ─── Stage 1: Frontend assets ────────────────────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ ./resources/
# public/ is needed so laravel-vite-plugin can write the manifest
COPY public/ ./public/

RUN npm run build

# ─── Stage 2: PHP vendor dependencies ────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ─── Stage 3: Production image ────────────────────────────────────────────────
FROM php:8.3-fpm-alpine

# System packages
# sqlite-dev provides sqlite3.h needed to compile pdo_sqlite
# libwebp-dev adds WebP support to GD (common export format from phones/Mac)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    libwebp-dev

# Build non-GD extensions first (easier to debug in isolation)
RUN docker-php-ext-install -j$(nproc) pdo pdo_sqlite zip

# Build GD with JPEG, PNG, FreeType, and WebP support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# Enable bundled opcache (already compiled in, just needs activating)
RUN docker-php-ext-enable opcache

# PHP production tuning
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

# Copy application source (vendor and build artefacts added below)
COPY . .

# Inject built assets and vendor from earlier stages
COPY --from=frontend /app/public/build ./public/build
COPY --from=vendor   /app/vendor       ./vendor

# Nginx and Supervisor config
COPY docker/nginx.conf      /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Ensure writable directories exist with correct ownership
RUN mkdir -p \
        storage/app/public \
        storage/framework/views \
        storage/framework/cache \
        storage/framework/sessions \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -sf http://localhost/up || exit 1

ENTRYPOINT ["/entrypoint.sh"]
