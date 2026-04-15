#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.docker.example .env
fi

if [ ! -x node_modules/.bin/vite ]; then
    npm install
fi

exec "$@"
