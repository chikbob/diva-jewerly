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

## Local Development

1. Start Docker:

```bash
docker compose up --build
```

2. Open the app:

- storefront: `http://localhost`
- Vite HMR: `http://localhost:5173`
- admin: `http://localhost/admin`

3. The app container will automatically:

- copy `.env.docker.example` to `.env` when needed
- install Composer dependencies
- install frontend dependencies
- generate an application key
- run migrations

## Test Commands

Run the backend test suite inside Docker:

```bash
docker compose run --rm -e APP_ENV=testing app php artisan test
```

Run the frontend production build:

```bash
docker compose run --rm vite npm run build
```

## Security Notes

- checkout no longer stores raw card numbers
- orders keep only payment method and generated payment reference
- password changes use Laravel's dedicated password update flow
- account deletion requires current password confirmation

## Project Structure

- `app/Http/Controllers` HTTP entry points
- `app/Http/Requests` request validation
- `app/Services` business logic orchestration
- `app/Models` Eloquent models
- `app/MoonShine` admin panel resources
- `resources/js` Inertia pages and Vue components
- `database/migrations` schema changes
- `tests` feature and unit tests

## CI

GitHub Actions validates:

- Composer install
- frontend dependency install
- Vite production build
- Laravel test suite
