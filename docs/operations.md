# Operations Runbook

## Health Endpoints

- `GET /live`: liveness probe. Confirms the HTTP stack is responsive.
- `GET /ready`: readiness probe. Confirms the application can talk to the database and cache, and checks failed-job / queue-backlog thresholds.
- `GET /metrics`: Prometheus-compatible metrics endpoint for runtime visibility, including HTTP/auth/checkout/queue counters and latency histograms.
- `GET /up`: legacy compatibility alias for the readiness probe.

Recommended usage:

- load balancer / container liveness: `curl -fsS https://example.com/live`
- deploy smoke check: `curl -fsS https://example.com/ready`
- post-release verification: `./scripts/smoke-check.sh https://example.com`
- metrics verification: `./scripts/metrics-check.sh https://example.com`
- monitoring config rendering: `./scripts/render-monitoring-config.sh`

Every response includes `X-Request-Id` so probe results can be correlated with structured logs.

## Environment Matrix

| Variable | Local Docker | CI / Tests | Production |
| --- | --- | --- | --- |
| `APP_ENV` | `local` | `testing` | `production` |
| `APP_DEBUG` | `true` | `false` | `false` |
| `LOG_CHANNEL` | `stderr` | `stack` or `stderr` | `stderr` |
| `LOG_STDERR_FORMATTER` | `Monolog\\Formatter\\JsonFormatter` | `Monolog\\Formatter\\JsonFormatter` | `Monolog\\Formatter\\JsonFormatter` |
| `METRICS_TOKEN` | empty | empty | random secret or ingress-level protection |
| `FAILED_JOBS_ALERT_THRESHOLD` | `5` | `5` or lower for targeted tests | agreed operational threshold |
| `QUEUE_BACKLOG_ALERT_THRESHOLD` | `50` | `50` or lower for targeted tests | agreed operational threshold |
| `HTTP_P95_SECONDS_ALERT_THRESHOLD` | `0.75` | `0.75` | agreed alert threshold |
| `QUEUE_JOB_P95_SECONDS_ALERT_THRESHOLD` | `30` | `30` | agreed alert threshold |
| `CHECKOUT_ERROR_RATE_ALERT_THRESHOLD` | `0.10` | `0.10` | agreed alert threshold |
| `LOGIN_FAILURE_RATE_ALERT_THRESHOLD` | `0.25` | `0.25` | agreed alert threshold |
| `SLO_AVAILABILITY_TARGET` | `99.9` | `99.9` | agreed SLO target |
| `SLO_HTTP_P95_SECONDS` | `0.5` | `0.5` | agreed SLO target |
| `SLO_CHECKOUT_SUCCESS_RATE` | `99` | `99` | agreed SLO target |
| `SLO_LOGIN_SUCCESS_RATE` | `95` | `95` | agreed SLO target |
| `SLO_QUEUE_JOB_P95_SECONDS` | `15` | `15` | agreed SLO target |
| `ALERTMANAGER_DEFAULT_RECEIVER` | `platform-default` | `platform-default` | production default receiver |
| `ALERTMANAGER_WARNING_RECEIVER` | `platform-warning` | `platform-warning` | production warning receiver |
| `ALERTMANAGER_CRITICAL_RECEIVER` | `platform-critical` | `platform-critical` | production critical receiver |
| `SESSION_SECURE_COOKIE` | `false` on plain HTTP | `false` | `true` |
| `SESSION_SAME_SITE` | `lax` | `lax` | `lax` or `strict` |
| `CORS_ALLOWED_ORIGINS` | local frontend origins only | empty or test host | exact production origins |
| `TRUSTED_PROXY_CIDRS` | empty unless behind proxy | empty | proxy/load balancer CIDRs |
| `TRUSTED_HOSTS` | `localhost,127.0.0.1` | test host | exact application hostnames |
| `CACHE_DRIVER` | `file` | `array` | shared cache such as Redis |
| `QUEUE_CONNECTION` | `redis` or `sync` | `sync` | queue backend suitable for retries |
| `QUEUE_RETRY_AFTER` | `120` | `90` | aligned with the slowest job runtime |
| `QUEUE_BLOCK_FOR` | `5` | `null` | positive value for Redis worker efficiency |
| `REDIS_HOST` | `redis` in Docker | empty unless used | shared Redis instance |

## Release Checklist

1. Pull the target commit and confirm CI passed.
2. Ensure production `.env` matches the matrix above.
3. Run migrations with a database backup already captured.
4. Verify Redis connectivity and start queue workers before opening traffic.
5. Publish assets if the deploy process does not run Composer hooks.
6. Run `./scripts/smoke-check.sh https://your-domain`.
7. Run `./scripts/metrics-check.sh https://your-domain`.
8. Render monitoring configs and sync them with your Prometheus / Alertmanager deployment.
9. Trigger `.github/workflows/post-deploy-checks.yml` or invoke it via `workflow_call` from the deploy pipeline.
10. Verify login, checkout and admin access with a real browser session.
11. Check recent logs, failed jobs count, queue backlog and latency histograms after the deploy.

## Backups And Recovery

Database backup helper:

```bash
DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=diva_jewelry DB_USERNAME=root DB_PASSWORD=root \
  ./scripts/backup-database.sh
```

Storage backup helper:

```bash
./scripts/backup-storage.sh
```

Post-deploy helper:

```bash
POST_DEPLOY_DB_BACKUP=1 POST_DEPLOY_STORAGE_BACKUP=1 \
  ./scripts/post-deploy.sh https://your-domain
```

Monitoring config helper:

```bash
./scripts/render-monitoring-config.sh deploy/monitoring/generated
```

Recovery outline:

1. Put the application into maintenance mode if needed.
2. Restore the database dump into an empty target database.
3. Restore uploaded files into `storage/app/public`.
4. Run `php artisan config:clear` and `php artisan cache:clear`.
5. Run `./scripts/smoke-check.sh http://your-host-or-load-balancer`.
6. Run `./scripts/metrics-check.sh http://your-host-or-load-balancer`.
7. Disable maintenance mode and monitor logs and health probes.

Restore helpers:

```bash
DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=diva_jewelry DB_USERNAME=root DB_PASSWORD=root \
  ./scripts/restore-database.sh backups/database/db-YYYY-MM-DD-HHMMSS.sql.gz --force

./scripts/restore-storage.sh backups/storage/storage-YYYY-MM-DD-HHMMSS.tar.gz storage/app/public --force
```
