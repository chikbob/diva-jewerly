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

exec "$@"
