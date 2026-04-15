#!/usr/bin/env bash

set -euo pipefail

base_url="${1:-}"

if [ -z "${base_url}" ]; then
    printf 'Usage: %s <base_url>\n' "$0" >&2
    exit 1
fi

if [ "${POST_DEPLOY_DB_BACKUP:-0}" = "1" ]; then
    ./scripts/backup-database.sh "${POST_DEPLOY_DB_BACKUP_DIR:-backups/database}"
fi

if [ "${POST_DEPLOY_STORAGE_BACKUP:-0}" = "1" ]; then
    ./scripts/backup-storage.sh "${POST_DEPLOY_STORAGE_SOURCE:-storage/app/public}" "${POST_DEPLOY_STORAGE_BACKUP_DIR:-backups/storage}"
fi

php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan view:cache

if [ "${QUEUE_CONNECTION:-sync}" != "sync" ]; then
    php artisan queue:restart || true
fi

./scripts/smoke-check.sh "${base_url}"
./scripts/metrics-check.sh "${base_url}"

printf 'Post-deploy automation completed for %s\n' "${base_url}"
