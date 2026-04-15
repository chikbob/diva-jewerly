#!/usr/bin/env bash

set -euo pipefail

backup_dir="${1:-backups/database}"
timestamp="$(date +%F-%H%M%S)"
filename="${backup_dir}/db-${timestamp}.sql.gz"

: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:=3306}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"

mkdir -p "${backup_dir}"

mysqldump_args=(
  --single-transaction
  --quick
  --host="${DB_HOST}" \
  --port="${DB_PORT}" \
  --user="${DB_USERNAME}" \
)

if mysqldump --help 2>/dev/null | grep -q 'set-gtid-purged'; then
    mysqldump_args+=(--set-gtid-purged=OFF)
fi

mysqldump_args+=("${DB_DATABASE}")

MYSQL_PWD="${DB_PASSWORD}" mysqldump "${mysqldump_args[@]}" | gzip -9 > "${filename}"

printf 'Database backup written to %s\n' "${filename}"
