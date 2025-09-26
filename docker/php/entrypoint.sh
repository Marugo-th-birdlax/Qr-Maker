#!/usr/bin/env bash
set -e

cd /var/www/html

# ให้สิทธิ์เขียน (เฉพาะครั้งแรก)
mkdir -p storage/framework/{cache,views,sessions} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# ถ้าไม่มี vendor ให้ติดตั้ง
if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

# ถ้าไม่มี APP_KEY ให้สร้าง
if [ -z "$APP_KEY" ] || grep -q "base64:.*" .env && [ $(php -r "echo empty(getenv('APP_KEY')) ? 0 : 1;") -eq 0 ]; then
  php artisan key:generate --force || true
fi

# Cache เบา ๆ (dev)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

exec "$@"
