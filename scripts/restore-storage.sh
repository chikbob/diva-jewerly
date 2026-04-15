#!/usr/bin/env bash

set -euo pipefail

backup_file="${1:-}"
target_dir="${2:-storage/app/public}"
force_flag="${3:-}"

if [ -z "${backup_file}" ] || [ ! -f "${backup_file}" ]; then
    printf 'Usage: %s <backup.tar.gz> [target_dir] --force\n' "$0" >&2
    exit 1
fi

if [ "${force_flag}" != "--force" ]; then
    printf 'Storage restore replaces files in the target directory. Re-run with --force.\n' >&2
    exit 1
fi

mkdir -p "${target_dir}"
find "${target_dir}" -mindepth 1 -maxdepth 1 -exec rm -rf {} +
tar -xzf "${backup_file}" -C "${target_dir}"

printf 'Storage restore completed into %s\n' "${target_dir}"
