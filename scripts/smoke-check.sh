#!/usr/bin/env bash

set -euo pipefail

base_url="${1:-http://localhost}"
base_url="${base_url%/}"
request_id="smoke-$(date +%s)"

probe() {
    local endpoint="$1"
    local response

    response="$(curl -fsS -H "X-Request-Id: ${request_id}" "${base_url}${endpoint}")"
    printf '%s\n' "${response}" | grep -q '"status":"ok"'
}

probe "/live"
probe "/ready"

printf 'Smoke checks passed for %s\n' "${base_url}"
