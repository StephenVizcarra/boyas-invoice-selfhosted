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
# The boyas_database volume shadows database/, so migration files baked into
# the image are invisible after first boot. Sync from /opt/migrations (a copy
# made at build time outside the volume mount) to pick up any new migrations.
cp -r /opt/migrations/* database/migrations/ 2>/dev/null || true
php artisan migrate --force --no-interaction
echo "[entrypoint] Migrations complete"

# ── 5. Storage directories and symlink ───────────────────────────────────────
mkdir -p storage/app/public storage/app/private/invoices
php artisan storage:link --force --no-interaction 2>/dev/null || true

# ── 6. Laravel caches ────────────────────────────────────────────────────────
php artisan config:cache  --no-interaction
php artisan route:cache   --no-interaction
php artisan view:cache    --no-interaction
echo "[entrypoint] Caches warmed"

# ── 7. Permissions (runs AFTER cache so generated files are included) ────────
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite

# ── 8. Hand off to Supervisor ────────────────────────────────────────────────
echo "[entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
