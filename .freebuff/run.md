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
