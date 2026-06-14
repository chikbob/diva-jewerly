# Docker Development Setup

## Quick Start

1. Open Docker Desktop and wait until it is running.
2. In the project root run:

```bash
docker compose up --build
```

3. Open the application at `http://localhost`.
4. Vite HMR is available at `http://localhost:5173`.

## Services

- `web`: Nginx reverse proxy exposed on port `80`
- `app`: Laravel PHP-FPM container with Composer and Node.js installed
- `vite`: Vite development server for Vue/Inertia assets
- `db`: MySQL 8 database exposed on port `3306`

## Environment

- If `.env` does not exist, the container copies `.env.example` to `.env`.
- Docker runtime overrides the DB settings so Laravel uses the `db` container.
- Default Docker database credentials:
  - database: `diva_jewelry`
  - user: `laravel`
  - password: `secret`
  - root password: `root`

## Notes

- Composer dependencies are stored in a named `vendor` volume.
- Frontend dependencies are stored in a named `node_modules` volume.
- Laravel migrations run automatically when the `app` container starts.
- `storage:link` is executed automatically when possible.
