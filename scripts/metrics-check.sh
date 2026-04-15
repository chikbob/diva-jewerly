#!/usr/bin/env bash

set -euo pipefail

base_url="${1:-http://localhost}"
base_url="${base_url%/}"
request_id="metrics-$(date +%s)"
curl_args=(-fsS -H "X-Request-Id: ${request_id}")

if [ -n "${METRICS_TOKEN:-}" ]; then
    curl_args+=(-H "Authorization: Bearer ${METRICS_TOKEN}")
fi

response="$(curl "${curl_args[@]}" "${base_url}/metrics")"

printf '%s\n' "${response}" | grep -q '^diva_app_liveness_status 1$'
printf '%s\n' "${response}" | grep -q '^diva_app_readiness_status '
printf '%s\n' "${response}" | grep -q '^diva_failed_jobs_total '
printf '%s\n' "${response}" | grep -q '^diva_queue_backlog_total'

printf 'Metrics checks passed for %s\n' "${base_url}"
