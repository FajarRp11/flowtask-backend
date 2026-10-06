#!/bin/sh
set -e

# Render mengirim port lewat env PORT (default 10000)
PORT="${PORT:-10000}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

# Cache config dibaca saat runtime supaya env var Render kepakai
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

# Jalankan migration yang belum jalan (set RUN_MIGRATIONS=false untuk mematikan)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec apache2-foreground
