#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.docker.example .env
fi

set_env_var() {
    key="$1"
    value="$2"

    if [ -z "$value" ]; then
        return
    fi

    escaped_value=$(printf '%s' "$value" | sed 's/[\/&]/\\&/g')

    if grep -q "^${key}=" .env; then
        sed -i "s/^${key}=.*/${key}=${escaped_value}/" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

set_env_var APP_ENV "${APP_ENV:-}"
set_env_var APP_DEBUG "${APP_DEBUG:-}"
set_env_var APP_URL "${APP_URL:-}"
set_env_var DB_CONNECTION "${DB_CONNECTION:-}"
set_env_var DB_HOST "${DB_HOST:-}"
set_env_var DB_PORT "${DB_PORT:-}"
set_env_var DB_DATABASE "${DB_DATABASE:-}"
set_env_var DB_USERNAME "${DB_USERNAME:-}"
set_env_var DB_PASSWORD "${DB_PASSWORD:-}"
set_env_var SESSION_SECURE_COOKIE "${SESSION_SECURE_COOKIE:-}"
set_env_var TRUSTED_HOSTS "${TRUSTED_HOSTS:-}"
set_env_var TRUSTED_PROXIES "${TRUSTED_PROXIES:-}"
set_env_var CORS_ALLOWED_ORIGINS "${CORS_ALLOWED_ORIGINS:-}"

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

if [ -n "${DB_HOST:-}" ] && [ -n "${DB_PORT:-}" ] && [ -n "${DB_USERNAME:-}" ] && [ -n "${DB_DATABASE:-}" ]; then
    until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD:-}" --silent; do
        echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
        sleep 2
    done

    php artisan migrate --force
fi

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link
fi

if [ ! -f public/vendor/moonshine/manifest.json ]; then
    php artisan vendor:publish --tag=moonshine-assets --force
fi

exec "$@"
