#!/bin/sh
set -eu

if [ ! -f /var/www/html/artisan ] && [ -d /opt/app-template ]; then
    mkdir -p /var/www/html
    cp -a /opt/app-template/. /var/www/html/
fi

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
set_env_var APP_KEY "${APP_KEY:-}"
set_env_var APP_DEBUG "${APP_DEBUG:-}"
set_env_var APP_URL "${APP_URL:-}"
set_env_var DB_CONNECTION "${DB_CONNECTION:-}"
set_env_var DB_HOST "${DB_HOST:-}"
set_env_var DB_PORT "${DB_PORT:-}"
set_env_var DB_DATABASE "${DB_DATABASE:-}"
set_env_var DB_USERNAME "${DB_USERNAME:-}"
set_env_var DB_PASSWORD "${DB_PASSWORD:-}"
set_env_var SESSION_DRIVER "${SESSION_DRIVER:-}"
set_env_var SESSION_SECURE_COOKIE "${SESSION_SECURE_COOKIE:-}"
set_env_var TRUSTED_HOSTS "${TRUSTED_HOSTS:-}"
set_env_var TRUSTED_PROXIES "${TRUSTED_PROXIES:-}"
set_env_var CORS_ALLOWED_ORIGINS "${CORS_ALLOWED_ORIGINS:-}"

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

until [ -z "${DB_HOST:-}" ] || [ -z "${DB_PORT:-}" ] || [ -z "${DB_USERNAME:-}" ] || [ -z "${DB_DATABASE:-}" ] || mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD:-}" --silent; do
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    sleep 2
done

if [ -f artisan ]; then
    php artisan migrate --force --ansi
fi

exec "$@"
