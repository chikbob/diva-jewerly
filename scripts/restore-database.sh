#!/usr/bin/env bash

set -euo pipefail

backup_file="${1:-}"
force_flag="${2:-}"

if [ -z "${backup_file}" ] || [ ! -f "${backup_file}" ]; then
    printf 'Usage: %s <backup.sql.gz|backup.sql> --force\n' "$0" >&2
    exit 1
fi

if [ "${force_flag}" != "--force" ]; then
    printf 'Database restore is destructive. Re-run with --force.\n' >&2
    exit 1
fi

: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:=3306}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"

case "${backup_file}" in
    *.gz)
        restore_cmd=(gunzip -c "${backup_file}")
        ;;
    *)
        restore_cmd=(cat "${backup_file}")
        ;;
esac

"${restore_cmd[@]}" | MYSQL_PWD="${DB_PASSWORD}" mysql \
  --host="${DB_HOST}" \
  --port="${DB_PORT}" \
  --user="${DB_USERNAME}" \
  "${DB_DATABASE}"

printf 'Database restore completed from %s\n' "${backup_file}"
