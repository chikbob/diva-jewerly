# Diva Jewelry

Laravel 10 + Inertia.js + Vue 3 application for a jewelry storefront with:

- product catalog and category landing pages
- authentication, profile management, password reset and email verification
- cart and checkout flows
- order history
- MoonShine admin panel
- Docker-based local development

## Stack

- PHP 8.1 / Laravel 10
- Vue 3 / Inertia.js / Vite / Tailwind CSS
- MySQL 8 for local development
- SQLite in-memory for automated tests
- MoonShine for back-office management
- Redis for cache and queue workers in Docker runtime

## Local Development

1. Start Docker:

```bash
docker compose up --build
```

2. Open the app:

- storefront: `http://localhost`
- Vite HMR: `http://localhost:5173`
- admin: `http://localhost/admin`
- liveness: `http://localhost/live`
- readiness: `http://localhost/ready`
- metrics: `http://localhost/metrics`
- compatibility health alias: `http://localhost/up`
- demo payment webhook: `POST http://localhost/api/payments/webhooks/demo_card`

3. The app container will automatically:

- copy `.env.docker.example` to `.env` when needed
- install Composer dependencies
- install frontend dependencies
- generate an application key
- run migrations
- publish MoonShine assets when they are missing

4. Background services started by Docker:

- `redis` for cache and queue transport
- `queue` worker for asynchronous jobs and failed-job capture

## Test Commands

Run the backend test suite inside Docker:

```bash
docker compose run --rm -e APP_ENV=testing app php artisan test
```

Run the frontend production build:

```bash
docker compose run --rm vite npm run build
```

Run HTTP smoke checks against a running environment:

```bash
./scripts/smoke-check.sh http://localhost
```

Validate runtime metrics after deploy:

```bash
./scripts/metrics-check.sh http://localhost
```

Reconcile payment state against stored provider transactions:

```bash
php artisan payments:reconcile
```

Render Prometheus and Alertmanager configuration from the current threshold env vars:

```bash
./scripts/render-monitoring-config.sh
```

Create a compressed database backup:

```bash
DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=diva_jewelry DB_USERNAME=root DB_PASSWORD=root \
./scripts/backup-database.sh
```

Create a storage backup:

```bash
./scripts/backup-storage.sh
```

Run post-deploy automation on the target host:

```bash
POST_DEPLOY_DB_BACKUP=1 POST_DEPLOY_STORAGE_BACKUP=1 \
./scripts/post-deploy.sh https://your-domain
```

## Security Notes

- checkout no longer stores raw card numbers
- orders keep only payment method and generated payment reference
- password changes use Laravel's dedicated password update flow
- account deletion requires current password confirmation
- CORS and session cookie behavior are now controlled through env variables instead of permissive hard-coded defaults
- every HTTP response now includes an `X-Request-Id` header for request correlation
- container logging uses JSON on `stderr` by default for production-friendly aggregation
- audit logs now cover auth, checkout and privileged admin access events with structured context
- Docker runtime now uses Redis-backed cache and queue profiles instead of file/sync defaults
- readiness now degrades on failed-jobs and queue-backlog thresholds, and `/metrics` exposes Prometheus-compatible operational signals
- `/metrics` now also exports HTTP/auth/checkout/queue counters and latency histograms for production monitoring
- generated Prometheus rules and Alertmanager routing can be rendered from local env vars with `scripts/render-monitoring-config.sh`

## Project Structure

- `app/Http/Controllers` HTTP entry points
- `app/Http/Requests` request validation
- `app/Services` business logic orchestration
- `app/Models` Eloquent models
- `app/MoonShine` admin panel resources
- `resources/js` Inertia pages and Vue components
- `database/migrations` schema changes
- `tests` feature and unit tests
- `docs/operations.md` release, backup, recovery and environment runbook
- `scripts` operational smoke-check, backup and restore helpers
- `.github/workflows/post-deploy-checks.yml` reusable post-deploy smoke/metrics verification workflow
- `config/operations.php` metrics token and alert threshold configuration

## CI

GitHub Actions validates:

- Composer install
- Composer audit
- frontend dependency install
- npm audit
- health routes registration
- metrics route registration
- Vite production build
- live HTTP smoke checks through `php artisan serve`
- runtime metrics checks through `/metrics`
- monitoring rule rendering
- Laravel test suite
