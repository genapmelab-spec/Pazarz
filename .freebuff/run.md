# How to run Pazarz

## Prerequisites
- PHP 8.2+ with required extensions
- MySQL database (database name from `.env`)
- Node.js (for frontend build)

## Reproduce artifacts
1. Copy `.env` from main checkout: `cp ../.env .`
2. Copy `frontend/.env` if needed
3. Build frontend: `cd frontend && npm install && npm run build`
4. Run migrations: `cd backend && php artisan migrate`
5. Run seeders: `cd backend && php artisan db:seed`

## Run server
```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

## URLs
- **Login**: `http://127.0.0.1:8000/dashboard/login` (redirected from `/`)
- **Admin Dashboard**: `http://127.0.0.1:8000/admin`
- **Seller Dashboard**: `http://127.0.0.1:8000/seller`
- **API**: `http://127.0.0.1:8000/api/v1/*`
- **Customer Frontend**: separate React app (run in frontend/)

## Login Credentials
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pazarz.com | password |
| Customer | customer@pazarz.com | password |
| Seller | seller1@pazarz.com | password |
