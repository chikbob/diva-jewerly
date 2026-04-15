# Operations Runbook

## Health Endpoints

- `GET /live`: liveness probe. Confirms the HTTP stack is responsive.
- `GET /ready`: readiness probe. Confirms the application can talk to the database and cache, and checks failed-job / queue-backlog thresholds.
- `GET /metrics`: Prometheus-compatible metrics endpoint for runtime visibility.
- `GET /up`: legacy compatibility alias for the readiness probe.

Recommended usage:

- load balancer / container liveness: `curl -fsS https://example.com/live`
- deploy smoke check: `curl -fsS https://example.com/ready`
- post-release verification: `./scripts/smoke-check.sh https://example.com`
- metrics verification: `./scripts/metrics-check.sh https://example.com`

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
8. Verify login, checkout and admin access with a real browser session.
9. Check recent logs, failed jobs count and queue backlog metrics after the deploy.

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
