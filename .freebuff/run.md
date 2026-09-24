# Run Guide

## Prerequisite: MySQL (Laragon bundled, NOT a Windows service)
MySQL does not auto-start; after a machine/Freebuff restart start it manually:
```powershell
powershell -NoProfile -Command "(Start-Process -FilePath 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqld.exe' -ArgumentList '--defaults-file=C:\laragon\bin\mysql\mysql-8.0.30-winx64\my.ini','--console' -RedirectStandardOutput '<log>' -RedirectStandardError '<log>.err' -WindowStyle Hidden -PassThru).Id"
```
Wait for `ready for connections` in the error log (port 3306). Without it, every Laravel page/API returns 500 (PDO 2002 connection refused).

## How to reproduce artifacts
1. From the main checkout, copy `.env` to `backend/.env` (if not present) — never commit; contains DB credentials
2. Install PHP dependencies: `cd backend && composer install`
3. Install JS dependencies: `cd frontend && npm install`
4. (Optional production build) `cd frontend && npm run build` → outputs to `backend/public/build`

## How to run the servers

### Backend (Laravel)
```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend (React/Vite dev server)
```bash
cd frontend
npm run dev
```

### Ports
- MySQL: 127.0.0.1:3306 (db `pazarz`, test db `pazarz_test`)
- Backend: http://127.0.0.1:8000 (admin + seller Blade dashboard + API)
- Frontend: http://localhost:5173 (customer React SPA; proxies /api + /sanctum to :8000)

## Smoke check
- `GET http://localhost:5173/api/v1/categories` → 200 proves React→proxy→Laravel→MySQL chain
- Backend slow (20s+) on very first request after boot is normal (Windows cold framework); steady state <1s

## Midtrans Sandbox (payments)
Credentials live in `backend/.env` (never commit): `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false`. The Server Key is backend-only — never expose it to the SPA.

After changing any of them: `cd backend && php artisan config:clear`.

Endpoints (all under `/api/v1`):
- `POST /orders/{orderNumber}/pay` (auth) -> Snap token + public client key
- `GET /orders/{orderNumber}/payment-status` (auth) -> server-verified status (also polls Midtrans server-to-server)
- `POST /payment/notification` (public) -> Midtrans webhook, signature-verified + idempotent

### Local webhook
Midtrans cannot reach `localhost`, so the status endpoint performs a server-to-server status lookup — a Sandbox payment still settles locally without a tunnel. To test the real webhook, expose the backend:
```bash
cloudflared tunnel --url http://127.0.0.1:8000
```
Then set the Payment Notification URL in the Midtrans Sandbox dashboard to `<tunnel>/api/v1/payment/notification`.

### Payment test scripts
```bash
cd backend
php artisan tinker --execute="require 'storage/midtrans_e2e.php';"          # checkout -> Snap token -> webhook -> paid
php artisan tinker --execute="require 'storage/midtrans_http_test.php';"     # HTTP contract the SPA uses
php artisan tinker --execute="require 'storage/midtrans_extra_test.php';"    # status sync + seller pay-gate
php artisan tinker --execute="require 'storage/cleanup_fixtures.php';"       # purge leftover test fixtures
```

## Two-Server Architecture (verified 2026-09-14)

Pazarz runs as TWO separate servers — never merge them onto one port:

| Layer | URL | What lives there |
|---|---|---|
| Frontend React (customer storefront) | http://localhost:5173 | Vite dev server (`npm run dev` in `frontend/`) |
| Backend Laravel (API + Admin/Seller dashboard + Midtrans) | http://127.0.0.1:8000 | `php artisan serve` in `backend/` |
| MySQL | 127.0.0.1:3306 | Laragon bundled mysqld (manual start, see above) |

Config contract (audit 2026-09-14):
- `backend/.env`: `APP_URL=http://127.0.0.1:8000`, `FRONTEND_URL=http://localhost:5173`, `SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:8000`
- `frontend/vite.config.ts`: dev proxy `/api` + `/sanctum` -> http://127.0.0.1:8000; React uses relative `baseURL: '/api/v1'`
- `backend/config/cors.php`: allows origins localhost:5173 / 127.0.0.1:5173 / both :8000 variants, credentials on
- Dashboard links from the storefront point at http://127.0.0.1:8000 (`Header.tsx`, `ProfilePage.tsx`)
- `routes/web.php` on :8000 serves ONLY dashboard/auth blades — no SPA catch-all. The React app is never served from :8000.

Smoke checks (all must pass):
1. `curl -s -o /dev/null -w "%{http_code} %{redirect_url}" http://127.0.0.1:8000/` -> 302 to /dashboard/login
2. `curl -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/dashboard/login` -> 200
3. `curl -o /dev/null -w "%{http_code}" http://localhost:5173/` -> 200
4. `curl -s http://localhost:5173/api/v1/categories | head -c 60` -> JSON (React -> Vite proxy -> Laravel)
5. `curl -sI -H "Origin: http://localhost:5173" http://127.0.0.1:8000/api/v1/products | grep access-control-allow-origin` -> echoes 5173
6. Midtrans: `POST /api/v1/orders/{no}/pay` -> 401 without auth; `POST /api/v1/payment/notification` -> 403 on bad signature. Snap token creation stays on Laravel (server key never leaves :8000).

## Product Variant Flow (fixed 2026-09-15)

Root cause: `product_attributes` table was empty — the seller create form renders
attribute inputs from `ProductAttribute::all()`, so no Size input ever appeared,
variants had no `product_attribute_values`, and the storefront (which builds its
Size selector from `variants[].attribute_values`) rendered nothing.

Fixes:
- `database/seeders/ProductAttributeSeeder.php` (new): seeds Size/Color attributes
  and backfills attribute values for existing multi-variant products by recovering
  the size from the SKU tail (e.g. PZR-CORD-BRN-M -> M). Registered in DatabaseSeeder.
- `frontend/src/features/catalog/ProductDetailPage.tsx`: out-of-stock sizes are now
  disabled (strikethrough, tooltip "Stok habis"); initial selection prefers the
  first in-stock variant.

Chain verified end-to-end (`backend/storage/variant_e2e.php`, 30 checks, self-cleaning):
product detail API (attrs+price+stock per variant) -> cart carries product_variant_id
+ price snapshot (out-of-stock add rejected 409) -> checkout order item snapshots
variant id + label -> Midtrans pay -> signed webhook -> payment success -> order paid
-> ONLY the bought variant's stock decremented (reserve at checkout, deduct on paid,
exactly once, idempotent duplicate webhook). `storage/cleanup_fixtures.php` also
gained a product_attribute_values step.

## Standard Sizes for No-Variant Products (2026-09-15)

Fashion products whose seller did NOT define variants now still get a standard
size choice S/M/L/XL on the storefront. Fully data-driven and persisted:

- `Product::STANDARD_SIZES` + `size_options` appended accessor (null unless the
  product is size-less AND its category chain starts with "fashion").
- `cart_items.chosen_size` (migration idempotent + FK-safe; InnoDB binds the
  composite unique index to FKs, so a dedicated cart_id index is created before
  dropping the unique, and re-dropped in down() once the unique is restored).
  Unique (cart_id, product_variant_id) relaxed to a normal index: same variant,
  different size = separate cart lines.
- CartService validates: size required for size_options products, must be one of
  the advertised options, uppercased; INVALID_SIZE -> 422. Wishlist to-cart path
  surfaces the same 422.
- CheckoutService appends chosen_size to order_items.variant_label_snapshot.
- Frontend: ProductDetailPage renders Ukuran S/M/L/XL from size_options, CTA
  disabled ("Pilih Ukuran") until a size is picked; cart/checkout show the size.

E2E `variant_e2e.php`: 49 checks (30 variant-path + 19 sizeless-path), all pass,
self-cleaning. Live-verified on vintage-washed-denim-jacket-90s.
