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

LOCKFILE_HASH_FILE="node_modules/.package-lock.sha256"
NEEDS_NPM_INSTALL="false"

if [ ! -x node_modules/.bin/vite ]; then
    NEEDS_NPM_INSTALL="true"
elif [ -f package-lock.json ]; then
    CURRENT_LOCKFILE_HASH="$(sha256sum package-lock.json | awk '{print $1}')"
    STORED_LOCKFILE_HASH="$(cat "$LOCKFILE_HASH_FILE" 2>/dev/null || true)"

    if [ "$CURRENT_LOCKFILE_HASH" != "$STORED_LOCKFILE_HASH" ]; then
        NEEDS_NPM_INSTALL="true"
    fi
fi

if [ "$NEEDS_NPM_INSTALL" = "true" ]; then
    npm install

    if [ -f package-lock.json ]; then
        mkdir -p node_modules
        sha256sum package-lock.json | awk '{print $1}' > "$LOCKFILE_HASH_FILE"
    fi
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
