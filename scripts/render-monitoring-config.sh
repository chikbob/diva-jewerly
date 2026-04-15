#!/usr/bin/env bash

set -euo pipefail

output_dir="${1:-deploy/monitoring/generated}"
mkdir -p "${output_dir}"

metrics_namespace="${METRICS_NAMESPACE:-diva}"
default_receiver="${ALERTMANAGER_DEFAULT_RECEIVER:-platform-default}"
warning_receiver="${ALERTMANAGER_WARNING_RECEIVER:-platform-warning}"
critical_receiver="${ALERTMANAGER_CRITICAL_RECEIVER:-platform-critical}"
http_p95_threshold="${HTTP_P95_SECONDS_ALERT_THRESHOLD:-0.75}"
queue_p95_threshold="${QUEUE_JOB_P95_SECONDS_ALERT_THRESHOLD:-30}"
checkout_error_threshold="${CHECKOUT_ERROR_RATE_ALERT_THRESHOLD:-0.10}"
login_failure_threshold="${LOGIN_FAILURE_RATE_ALERT_THRESHOLD:-0.25}"
failed_jobs_threshold="${FAILED_JOBS_ALERT_THRESHOLD:-5}"
queue_backlog_threshold="${QUEUE_BACKLOG_ALERT_THRESHOLD:-50}"

cat > "${output_dir}/prometheus-rules.yml" <<EOF
groups:
  - name: ${metrics_namespace}-runtime
    interval: 30s
    rules:
      - alert: DivaReadinessDegraded
        expr: max_over_time(${metrics_namespace}_app_readiness_status[5m]) < 1
        for: 2m
        labels:
          severity: critical
          service: diva
        annotations:
          summary: Diva readiness is degraded
          description: One or more readiness checks have been failing for at least 2 minutes.

      - alert: DivaFailedJobsThresholdExceeded
        expr: ${metrics_namespace}_failed_jobs_total > ${failed_jobs_threshold}
        for: 5m
        labels:
          severity: warning
          service: diva
        annotations:
          summary: Diva failed jobs threshold exceeded
          description: Failed jobs count is above ${failed_jobs_threshold}.

      - alert: DivaQueueBacklogHigh
        expr: max(${metrics_namespace}_queue_backlog_total) > ${queue_backlog_threshold}
        for: 10m
        labels:
          severity: warning
          service: diva
        annotations:
          summary: Diva queue backlog is above threshold
          description: Queue backlog has been above ${queue_backlog_threshold} for at least 10 minutes.

      - alert: DivaHttpLatencyHigh
        expr: histogram_quantile(0.95, sum by (le) (rate(${metrics_namespace}_http_request_duration_seconds_bucket[10m]))) > ${http_p95_threshold}
        for: 10m
        labels:
          severity: warning
          service: diva
        annotations:
          summary: Diva HTTP p95 latency is above threshold
          description: HTTP p95 latency exceeded ${http_p95_threshold}s over the last 10 minutes.

      - alert: DivaCheckoutErrorRateHigh
        expr: sum(rate(${metrics_namespace}_checkout_orders_total{outcome="failure"}[10m])) / clamp_min(sum(rate(${metrics_namespace}_checkout_orders_total[10m])), 1) > ${checkout_error_threshold}
        for: 10m
        labels:
          severity: critical
          service: diva
        annotations:
          summary: Diva checkout error rate is too high
          description: Checkout error rate exceeded ${checkout_error_threshold} over the last 10 minutes.

      - alert: DivaLoginFailureRateHigh
        expr: sum(rate(${metrics_namespace}_auth_events_total{event="login_failed"}[10m])) / clamp_min(sum(rate(${metrics_namespace}_auth_events_total{event=~"login_failed|login_succeeded"}[10m])), 1) > ${login_failure_threshold}
        for: 10m
        labels:
          severity: warning
          service: diva
        annotations:
          summary: Diva login failure rate is too high
          description: Login failure rate exceeded ${login_failure_threshold} over the last 10 minutes.

      - alert: DivaQueueRuntimeHigh
        expr: histogram_quantile(0.95, sum by (le) (rate(${metrics_namespace}_queue_job_duration_seconds_bucket[10m]))) > ${queue_p95_threshold}
        for: 10m
        labels:
          severity: warning
          service: diva
        annotations:
          summary: Diva queue runtime p95 is above threshold
          description: Queue job p95 runtime exceeded ${queue_p95_threshold}s over the last 10 minutes.
EOF

cat > "${output_dir}/alertmanager.yml" <<EOF
route:
  receiver: ${default_receiver}
  group_by: ['alertname', 'severity', 'service']
  group_wait: 30s
  group_interval: 5m
  repeat_interval: 2h
  routes:
    - matchers:
        - severity="critical"
      receiver: ${critical_receiver}
    - matchers:
        - severity="warning"
      receiver: ${warning_receiver}

receivers:
  - name: ${default_receiver}
    webhook_configs:
      - url: http://alerts-router.internal/default
        send_resolved: true

  - name: ${warning_receiver}
    webhook_configs:
      - url: http://alerts-router.internal/warning
        send_resolved: true

  - name: ${critical_receiver}
    webhook_configs:
      - url: http://alerts-router.internal/critical
        send_resolved: true
EOF

printf 'Monitoring configs rendered into %s\n' "${output_dir}"
