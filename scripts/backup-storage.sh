#!/usr/bin/env bash

set -euo pipefail

source_dir="${1:-storage/app/public}"
backup_dir="${2:-backups/storage}"
timestamp="$(date +%F-%H%M%S)"
filename="${backup_dir}/storage-${timestamp}.tar.gz"

if [ ! -d "${source_dir}" ]; then
    printf 'Storage source directory not found: %s\n' "${source_dir}" >&2
    exit 1
fi

mkdir -p "${backup_dir}"

tar -czf "${filename}" -C "${source_dir}" .

printf 'Storage backup written to %s\n' "${filename}"
