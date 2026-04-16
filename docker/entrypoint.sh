#!/bin/sh
set -e

APP_DIR="/var/www/html"
cd "$APP_DIR"

# ── 1. Bootstrap .env ────────────────────────────────────────────────────────
if [ ! -f .env ]; then
    cp .env.example .env
    echo "[entrypoint] Created .env from .env.example"
fi

# Override a subset of settings for container use
sed -i 's|^APP_ENV=.*|APP_ENV=production|' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|' .env
sed -i 's|^LOG_LEVEL=.*|LOG_LEVEL=error|' .env
# This project only has the 3 custom app migrations — the default Laravel
# migrations for sessions/cache/jobs tables were never added. Switch both
# drivers to file-based so no extra DB tables are needed.
sed -i 's|^SESSION_DRIVER=.*|SESSION_DRIVER=file|' .env
sed -i 's|^CACHE_STORE=.*|CACHE_STORE=file|' .env

# ── 2. App key ───────────────────────────────────────────────────────────────
# If APP_KEY is passed as a Docker env var, write it into .env so artisan can
# read it. If neither is set, generate a fresh key (sessions will reset on
# container restart unless you persist .env or always pass APP_KEY).
if [ -n "$APP_KEY" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
    echo "[entrypoint] APP_KEY applied from environment"
else
    CURRENT_KEY=$(grep -E '^APP_KEY=' .env | cut -d= -f2-)
    if [ -z "$CURRENT_KEY" ]; then
        php artisan key:generate --force --no-interaction
        echo "[entrypoint] Generated new APP_KEY"
    fi
fi

# ── 3. SQLite database ───────────────────────────────────────────────────────
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "[entrypoint] Created fresh database/database.sqlite"
fi

# ── 4. Migrations ────────────────────────────────────────────────────────────
php artisan migrate --force --no-interaction
echo "[entrypoint] Migrations complete"

# ── 5. Storage symlink ───────────────────────────────────────────────────────
mkdir -p storage/app/public
php artisan storage:link --force --no-interaction 2>/dev/null || true

# ── 6. Permissions (important if volumes are mounted as root) ────────────────
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite

# ── 7. Laravel caches ────────────────────────────────────────────────────────
php artisan config:cache  --no-interaction
php artisan route:cache   --no-interaction
php artisan view:cache    --no-interaction
echo "[entrypoint] Caches warmed"

# ── 8. Hand off to Supervisor ────────────────────────────────────────────────
echo "[entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
