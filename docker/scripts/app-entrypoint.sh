#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.docker.example .env
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -x node_modules/.bin/vite ]; then
    npm install
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

until mysqladmin ping -h"${DB_HOST:-db}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-laravel}" -p"${DB_PASSWORD:-secret}" --silent; do
    echo "Waiting for MySQL at ${DB_HOST:-db}:${DB_PORT:-3306}..."
    sleep 2
done

php artisan migrate --force

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link
fi

if [ ! -f public/vendor/moonshine/manifest.json ]; then
    php artisan vendor:publish --tag=moonshine-assets --force
fi

exec "$@"
