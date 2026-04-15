# Operations Runbook

## Health Endpoints

- `GET /live`: liveness probe. Confirms the HTTP stack is responsive.
- `GET /ready`: readiness probe. Confirms the application can talk to the database and cache.
- `GET /up`: legacy compatibility alias for the readiness probe.

Recommended usage:

- load balancer / container liveness: `curl -fsS https://example.com/live`
- deploy smoke check: `curl -fsS https://example.com/ready`
- post-release verification: `./scripts/smoke-check.sh https://example.com`

Every response includes `X-Request-Id` so probe results can be correlated with structured logs.

## Environment Matrix

| Variable | Local Docker | CI / Tests | Production |
| --- | --- | --- | --- |
| `APP_ENV` | `local` | `testing` | `production` |
| `APP_DEBUG` | `true` | `false` | `false` |
| `LOG_CHANNEL` | `stderr` | `stack` or `stderr` | `stderr` |
| `LOG_STDERR_FORMATTER` | `Monolog\\Formatter\\JsonFormatter` | `Monolog\\Formatter\\JsonFormatter` | `Monolog\\Formatter\\JsonFormatter` |
| `SESSION_SECURE_COOKIE` | `false` on plain HTTP | `false` | `true` |
| `SESSION_SAME_SITE` | `lax` | `lax` | `lax` or `strict` |
| `CORS_ALLOWED_ORIGINS` | local frontend origins only | empty or test host | exact production origins |
| `TRUSTED_PROXY_CIDRS` | empty unless behind proxy | empty | proxy/load balancer CIDRs |
| `TRUSTED_HOSTS` | `localhost,127.0.0.1` | test host | exact application hostnames |
| `CACHE_DRIVER` | `file` | `array` | shared cache such as Redis |
| `QUEUE_CONNECTION` | `sync` | `sync` | queue backend suitable for retries |

## Release Checklist

1. Pull the target commit and confirm CI passed.
2. Ensure production `.env` matches the matrix above.
3. Run migrations with a database backup already captured.
4. Publish assets if the deploy process does not run Composer hooks.
5. Run `./scripts/smoke-check.sh https://your-domain`.
6. Verify login, checkout and admin access with a real browser session.
7. Check recent logs for `warning` or `error` entries after the deploy.

## Backups And Recovery

Database backup example:

```bash
mysqldump --single-transaction --quick --set-gtid-purged=OFF \
  -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  > "backup-$(date +%F-%H%M%S).sql"
```

Uploaded file backup example:

```bash
tar -czf "storage-backup-$(date +%F-%H%M%S).tar.gz" storage/app/public
```

Recovery outline:

1. Put the application into maintenance mode if needed.
2. Restore the database dump into an empty target database.
3. Restore uploaded files into `storage/app/public`.
4. Run `php artisan config:clear` and `php artisan cache:clear`.
5. Run `./scripts/smoke-check.sh http://your-host-or-load-balancer`.
6. Disable maintenance mode and monitor logs and health probes.
