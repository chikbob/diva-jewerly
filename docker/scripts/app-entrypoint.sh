#!/bin/sh
set -eu

if [ ! -f /var/www/html/artisan ] && [ -d /opt/app-template ]; then
    mkdir -p /var/www/html
    cp -a /opt/app-template/. /var/www/html/
fi

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null && [ -z "${APP_KEY:-}" ]; then
    APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
    export APP_KEY
fi

if [ -z "${DB_CONNECTION:-}" ]; then
    case "${DATABASE_URL:-}" in
        postgres://*|postgresql://*|pgsql://*)
            DB_CONNECTION=pgsql
            export DB_CONNECTION
            ;;
    esac
fi

if [ "${DB_CONNECTION:-}" = "pgsql" ]; then
    : "${DB_HOST:=${PGHOST:-}}"
    : "${DB_PORT:=${PGPORT:-5432}}"
    : "${DB_DATABASE:=${PGDATABASE:-}}"
    : "${DB_USERNAME:=${PGUSER:-}}"
    : "${DB_PASSWORD:=${PGPASSWORD:-}}"

    export DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
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
set_env_var APP_NAME "${APP_NAME:-}"
set_env_var APP_KEY "${APP_KEY:-}"
set_env_var APP_DEBUG "${APP_DEBUG:-}"
set_env_var APP_URL "${APP_URL:-}"
set_env_var DATABASE_URL "${DATABASE_URL:-}"
set_env_var DB_CONNECTION "${DB_CONNECTION:-}"
set_env_var DB_HOST "${DB_HOST:-}"
set_env_var DB_PORT "${DB_PORT:-}"
set_env_var DB_DATABASE "${DB_DATABASE:-}"
set_env_var DB_USERNAME "${DB_USERNAME:-}"
set_env_var DB_PASSWORD "${DB_PASSWORD:-}"
set_env_var CACHE_DRIVER "${CACHE_DRIVER:-}"
set_env_var QUEUE_CONNECTION "${QUEUE_CONNECTION:-}"
set_env_var SESSION_DRIVER "${SESSION_DRIVER:-}"
set_env_var SESSION_SECURE_COOKIE "${SESSION_SECURE_COOKIE:-}"
set_env_var SESSION_SAME_SITE "${SESSION_SAME_SITE:-}"
set_env_var TRUSTED_HOSTS "${TRUSTED_HOSTS:-}"
set_env_var TRUSTED_PROXIES "${TRUSTED_PROXIES:-}"
set_env_var CORS_ALLOWED_ORIGINS "${CORS_ALLOWED_ORIGINS:-}"
set_env_var REDIS_URL "${REDIS_URL:-}"
set_env_var REDIS_HOST "${REDIS_HOST:-}"
set_env_var REDIS_PORT "${REDIS_PORT:-}"
set_env_var REDIS_USERNAME "${REDIS_USERNAME:-}"
set_env_var REDIS_PASSWORD "${REDIS_PASSWORD:-}"
set_env_var REDIS_CLIENT "${REDIS_CLIENT:-}"

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

case "${DB_CONNECTION:-}" in
    pgsql)
        export PGPASSWORD="${DB_PASSWORD:-}"

        until [ -z "${DB_HOST:-}" ] || [ -z "${DB_PORT:-}" ] || [ -z "${DB_USERNAME:-}" ] || [ -z "${DB_DATABASE:-}" ] || pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" > /dev/null 2>&1; do
            echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
            sleep 2
        done
        ;;
    mysql)
        until [ -z "${DB_HOST:-}" ] || [ -z "${DB_PORT:-}" ] || [ -z "${DB_USERNAME:-}" ] || [ -z "${DB_DATABASE:-}" ] || mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD:-}" --silent; do
            echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
            sleep 2
        done
        ;;
esac

if [ -f artisan ]; then
    php artisan migrate --force --ansi
fi

exec "$@"
