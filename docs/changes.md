# Project Changes

## What Was Improved

- made `DatabaseSeeder` idempotent so repeated `php artisan migrate --seed` no longer fails
- moved catalog filtering into `CatalogService` and added dedicated `CatalogIndexRequest` / `OrderIndexRequest`
- invalidated cached catalog price range when products change
- improved cart UX: no quantity below `1`, product links in cart, clearer flash messages
- added product detail page at `GET /products/{product}`
- added wishlist/favorites with header counter, catalog/detail actions and a dedicated page
- added `repeat order` flow that restores order items into the cart
- added order detail page at `GET /orders/{order}` with safe rendering of archived/deleted products
- localized storefront and auth pages to Ukrainian and added Ukrainian validation/auth translations
- improved MoonShine order resource with default newest sorting, status badges, filters and better searchability
- added read-only MoonShine payments resource with filters, search and policy protection
- added a new custom commercial backoffice at `GET /admin` with CRUD over core business tables
- moved MoonShine from `/admin` to `/moonshine` to preserve the package without blocking the custom backoffice

## New Features

- product detail page with image, category, description, price and add-to-cart action
- catalog filter for `only_new` products from the last 30 days
- favorites page at `GET /favorites`
- repeat-order action from order history
- order detail page for authenticated customers
- payments overview in MoonShine admin
- custom admin panel on `/admin` with dashboard, staff login and generic CRUD screens

## Run Locally

```bash
docker compose up --build
```

Open:

- storefront: `http://localhost`
- admin: `http://localhost/admin`
- moonshine internal admin: `http://localhost/moonshine`
- live: `http://localhost/live`
- ready: `http://localhost/ready`
- up: `http://localhost/up`
- metrics: `http://localhost/metrics`

## Verification Commands

```bash
docker compose exec -T app composer install --no-interaction --prefer-dist
docker compose exec -T app php artisan migrate --seed --force
docker compose exec -T app php artisan test
docker compose exec -T app php artisan route:list
docker compose run --rm -e APP_ENV=testing app php artisan test
docker compose run --rm vite npm run build
```

Latest verification result:

- `68` tests passed
- `323` assertions passed

## Notes

- the local machine used for this pass did not have host `php`/`composer`, so backend checks were executed through Docker
- local `npm run build` on the synced workspace was inconsistent, so the authoritative frontend build check was `docker compose run --rm vite npm run build`
- guest access now redirects from `/admin` to `/admin/login`, while MoonShine login remains at `/moonshine/login`
