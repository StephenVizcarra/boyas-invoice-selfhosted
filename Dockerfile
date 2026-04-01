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
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    curl

# PHP extensions required by Laravel + DomPDF
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_sqlite \
        zip \
        opcache

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
